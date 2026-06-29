import json
import base64
import os
from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.staticfiles import StaticFiles
from fastapi.responses import RedirectResponse
from sqlalchemy.orm import Session
from typing import List

import models
import schemas
from database import engine, get_db
from ai_engine import verify_faces, extract_face_landmarks, match_tutors

# Create Database tables
models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="TutorMatch AI Portal", description="Tutor Matching and Face Verification System")

# Helper to decode base64 string
def decode_base64_image(base64_str: str) -> bytes:
    try:
        if "," in base64_str:
            base64_str = base64_str.split(",")[1]
        return base64.b64decode(base64_str)
    except Exception as e:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"รูปแบบข้อมูลรูปภาพไม่ถูกต้อง: {str(e)}"
        )

# API: Register User
@app.post("/api/register", response_model=schemas.UserResponse)
def register_user(user: schemas.UserCreate, db: Session = Depends(get_db)):
    db_user = db.query(models.User).filter(models.User.username == user.username).first()
    if db_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ชื่อผู้ใช้งานนี้ถูกใช้ไปแล้ว"
        )
    
    new_user = models.User(
        username=user.username,
        name=user.name,
        role=user.role
    )
    db.add(new_user)
    db.commit()
    db.refresh(new_user)
    return new_user

# API: Register Biometrics (Face)
@app.post("/api/biometrics/register")
def register_face(req: schemas.FaceRegisterRequest, db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.username == req.username).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบผู้ใช้งานในระบบ"
        )
        
    image_bytes = decode_base64_image(req.image_base64)
    landmarks = extract_face_landmarks(image_bytes)
    
    if landmarks is None:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="ไม่สามารถตรวจจับใบหน้าในรูปภาพได้ กรุณาถ่ายภาพหน้าตรงให้ชัดเจน"
        )
        
    # Save face landmarks as JSON text or face image (now base64 string from LBPH extract)
    landmarks_json = landmarks # In LBPH it is a base64 string
    
    # Check if biometric profile already exists
    biometric = db.query(models.BiometricProfile).filter(models.BiometricProfile.user_id == user.id).first()
    if biometric:
        biometric.face_landmarks = landmarks_json
    else:
        biometric = models.BiometricProfile(user_id=user.id, face_landmarks=landmarks_json)
        db.add(biometric)
        
    db.commit()
    return {"status": "success", "message": "ลงทะเบียนข้อมูลสแกนใบหน้าสำเร็จแล้ว"}

# API: Verify Face (Login/Auth)
@app.post("/api/biometrics/verify")
def verify_face(req: schemas.FaceVerifyRequest, db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.username == req.username).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบผู้ใช้งานในระบบ"
        )
        
    biometric = db.query(models.BiometricProfile).filter(models.BiometricProfile.user_id == user.id).first()
    if not biometric:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ผู้ใช้นี้ยังไม่ได้ลงทะเบียนข้อมูลใบหน้าเข้าระบบ"
        )
        
    image_bytes = decode_base64_image(req.image_base64)
    
    # Verify landmarks (LBPH)
    # Threshold for LBPH is typically 75.0 (lower is more strict)
    verified, distance, error_msg = verify_faces(biometric.face_landmarks, image_bytes, threshold=75.0)
    
    if error_msg:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=error_msg
        )
        
    if not verified:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=f"การยืนยันใบหน้าไม่สำเร็จ (Distance: {distance:.2f}) กรุณาลองใหม่อีกครั้ง"
        )
        
    return {
        "status": "success",
        "message": "ยืนยันตัวตนสำเร็จ",
        "distance": distance,
        "user": {
            "id": user.id,
            "username": user.username,
            "name": user.name,
            "role": user.role
        }
    }

# API: Save Tutor Profile
@app.post("/api/tutor/profile", response_model=schemas.TutorProfileResponse)
def save_tutor_profile(
    profile: schemas.TutorProfileCreate,
    username: str, # Sent via query parameters for simulation
    db: Session = Depends(get_db)
):
    user = db.query(models.User).filter(models.User.username == username).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบผู้ใช้งานในระบบ"
        )
        
    if user.role != "tutor":
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="บัญชีผู้ใช้นี้ไม่ใช่บัญชีผู้สอน (Tutor)"
        )
        
    db_profile = db.query(models.TutorProfile).filter(models.TutorProfile.user_id == user.id).first()
    
    # Prep combined text vector index
    vector_text = f"{profile.subjects} {profile.expertise} {profile.teaching_style} {profile.education} {profile.availability}"
    
    if db_profile:
        db_profile.education = profile.education
        db_profile.subjects = profile.subjects
        db_profile.expertise = profile.expertise
        db_profile.teaching_style = profile.teaching_style
        db_profile.price_per_hour = profile.price_per_hour
        db_profile.availability = profile.availability
        db_profile.contact_line = profile.contact_line
        db_profile.contact_phone = profile.contact_phone
        db_profile.contact_email = profile.contact_email
        db_profile.appointment_instruction = profile.appointment_instruction
        db_profile.profile_vector_text = vector_text
    else:
        db_profile = models.TutorProfile(
            user_id=user.id,
            education=profile.education,
            subjects=profile.subjects,
            expertise=profile.expertise,
            teaching_style=profile.teaching_style,
            price_per_hour=profile.price_per_hour,
            availability=profile.availability,
            contact_line=profile.contact_line,
            contact_phone=profile.contact_phone,
            contact_email=profile.contact_email,
            appointment_instruction=profile.appointment_instruction,
            profile_vector_text=vector_text
        )
        db.add(db_profile)
        
    db.commit()
    db.refresh(db_profile)
    return db_profile

# API: Get List of all Tutors
@app.get("/api/tutors")
def get_all_tutors(db: Session = Depends(get_db)):
    tutor_profiles = db.query(models.TutorProfile).all()
    results = []
    for p in tutor_profiles:
        results.append({
            "user_id": p.user_id,
            "name": p.user.name,
            "education": p.education,
            "subjects": p.subjects,
            "expertise": p.expertise,
            "teaching_style": p.teaching_style,
            "price_per_hour": p.price_per_hour,
            "availability": p.availability,
            "contact_line": p.contact_line or "-",
            "contact_phone": p.contact_phone or "-",
            "contact_email": p.contact_email or "-",
            "appointment_instruction": p.appointment_instruction or "ติดต่อผ่านช่องทางการติดต่อหลัก"
        })
    return results

# API: Search & Match Tutors (AI Matching)
@app.post("/api/student/match", response_model=List[schemas.MatchedTutorResponse])
def search_and_match_tutors(req: schemas.StudentMatchRequest, db: Session = Depends(get_db)):
    student = db.query(models.User).filter(models.User.username == req.username).first()
    if not student:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบข้อมูลผู้เรียนในระบบ"
        )
        
    # Get all tutors in db
    tutor_profiles = db.query(models.TutorProfile).all()
    if not tutor_profiles:
        return []
        
    tutor_list = []
    for p in tutor_profiles:
        tutor_list.append({
            "user_id": p.user_id,
            "name": p.user.name,
            "education": p.education,
            "subjects": p.subjects,
            "expertise": p.expertise,
            "teaching_style": p.teaching_style,
            "price_per_hour": p.price_per_hour,
            "availability": p.availability,
            "contact_line": p.contact_line or "-",
            "contact_phone": p.contact_phone or "-",
            "contact_email": p.contact_email or "-",
            "appointment_instruction": p.appointment_instruction or "ติดต่อผู้สอนโดยตรง",
            "profile_text": p.profile_vector_text or ""
        })
        
    # Compute Match Score using NLP Similarity
    matched_results = match_tutors(
        student_req=req.requirements_text,
        tutors=tutor_list,
        budget=req.budget,
        subject_filter=req.subject_filter
    )
    
    # Save the highest match history (if results found)
    if matched_results:
        best_match = matched_results[0]
        history = models.MatchHistory(
            student_id=student.id,
            requirements_text=req.requirements_text,
            matched_tutor_id=best_match['user_id'],
            score=best_match['match_score']
        )
        db.add(history)
        db.commit()
        
    return matched_results

# API: Create Lesson Appointment
@app.post("/api/appointments", response_model=schemas.AppointmentResponse)
def create_appointment(req: schemas.AppointmentCreate, db: Session = Depends(get_db)):
    student = db.query(models.User).filter(models.User.username == req.student_username).first()
    if not student:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบข้อมูลผู้เรียนในระบบ"
        )
        
    tutor = db.query(models.User).filter(models.User.id == req.tutor_id).first()
    if not tutor:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบข้อมูลติวเตอร์ในระบบ"
        )
        
    appointment = models.Appointment(
        student_id=student.id,
        tutor_id=tutor.id,
        appointment_date=req.appointment_date,
        appointment_time=req.appointment_time,
        notes=req.notes
    )
    
    db.add(appointment)
    db.commit()
    db.refresh(appointment)
    
    # Map to schema response
    return schemas.AppointmentResponse(
        id=appointment.id,
        student_id=appointment.student_id,
        student_name=student.name,
        tutor_id=appointment.tutor_id,
        tutor_name=tutor.name,
        appointment_date=appointment.appointment_date,
        appointment_time=appointment.appointment_time,
        notes=appointment.notes,
        status=appointment.status,
        created_at=appointment.created_at
    )

# API: List Appointments for a User
@app.get("/api/appointments", response_model=List[schemas.AppointmentResponse])
def get_appointments(username: str, db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.username == username).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="ไม่พบข้อมูลผู้ใช้งานในระบบ"
        )
        
    if user.role == "tutor":
        appointments = db.query(models.Appointment).filter(models.Appointment.tutor_id == user.id).all()
    else:
        appointments = db.query(models.Appointment).filter(models.Appointment.student_id == user.id).all()
        
    results = []
    for appt in appointments:
        results.append(schemas.AppointmentResponse(
            id=appt.id,
            student_id=appt.student_id,
            student_name=appt.student.name,
            tutor_id=appt.tutor_id,
            tutor_name=appt.tutor.name,
            appointment_date=appt.appointment_date,
            appointment_time=appt.appointment_time,
            notes=appt.notes,
            status=appt.status,
            created_at=appt.created_at
        ))
    return results

# Redirect Root to LIFF Simulator
@app.get("/")
def read_root():
    return RedirectResponse(url="/static/index.html")

# Create static directory if it doesn't exist
os.makedirs("static", exist_ok=True)

# Mount static files
app.mount("/static", StaticFiles(directory="static"), name="static")
