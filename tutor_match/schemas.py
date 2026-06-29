from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime

class UserCreate(BaseModel):
    username: str
    name: str
    role: str # 'student' or 'tutor'

class UserResponse(BaseModel):
    id: int
    username: str
    name: str
    role: str

    class Config:
        from_attributes = True

class FaceRegisterRequest(BaseModel):
    username: str
    image_base64: str # Base64 encoded image captured from webcam

class FaceVerifyRequest(BaseModel):
    username: str
    image_base64: str

class TutorProfileCreate(BaseModel):
    education: str
    subjects: str
    expertise: str
    teaching_style: str
    price_per_hour: int
    availability: str
    contact_line: Optional[str] = None
    contact_phone: Optional[str] = None
    contact_email: Optional[str] = None
    appointment_instruction: Optional[str] = None

class TutorProfileResponse(BaseModel):
    id: int
    user_id: int
    education: str
    subjects: str
    expertise: str
    teaching_style: str
    price_per_hour: int
    availability: str
    contact_line: Optional[str] = None
    contact_phone: Optional[str] = None
    contact_email: Optional[str] = None
    appointment_instruction: Optional[str] = None

    class Config:
        from_attributes = True

class StudentMatchRequest(BaseModel):
    username: str # Verified user
    requirements_text: str
    budget: Optional[int] = None
    subject_filter: Optional[str] = None

class MatchedTutorResponse(BaseModel):
    user_id: int
    name: str
    education: str
    subjects: str
    expertise: str
    teaching_style: str
    price_per_hour: int
    availability: str
    contact_line: Optional[str] = None
    contact_phone: Optional[str] = None
    contact_email: Optional[str] = None
    appointment_instruction: Optional[str] = None
    match_score: float

# Appointment Schemas
class AppointmentCreate(BaseModel):
    student_username: str
    tutor_id: int
    appointment_date: str # YYYY-MM-DD
    appointment_time: str # Slot
    notes: Optional[str] = None

class AppointmentResponse(BaseModel):
    id: int
    student_id: int
    student_name: str
    tutor_id: int
    tutor_name: str
    appointment_date: str
    appointment_time: str
    notes: Optional[str] = None
    status: str
    created_at: datetime

    class Config:
        from_attributes = True
