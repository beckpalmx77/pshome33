from sqlalchemy import Column, Integer, String, Float, Text, ForeignKey, DateTime
from sqlalchemy.orm import relationship
from datetime import datetime
from database import Base

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String, unique=True, index=True, nullable=False)
    name = Column(String, nullable=False)
    role = Column(String, nullable=False) # 'student' or 'tutor'
    line_id = Column(String, unique=True, nullable=True) # LINE ID if integrated
    registered_at = Column(DateTime, default=datetime.utcnow)

    # Relationships
    biometric_profile = relationship("BiometricProfile", uselist=False, back_populates="user", cascade="all, delete-orphan")
    tutor_profile = relationship("TutorProfile", uselist=False, back_populates="user", cascade="all, delete-orphan")

class BiometricProfile(Base):
    __tablename__ = "biometric_profiles"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), unique=True)
    face_landmarks = Column(Text, nullable=False) # JSON encoded coordinates of normalized face landmarks
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    user = relationship("User", back_populates="biometric_profile")

class TutorProfile(Base):
    __tablename__ = "tutor_profiles"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), unique=True)
    education = Column(String, nullable=False)
    subjects = Column(String, nullable=False) # Comma-separated main subjects
    expertise = Column(Text, nullable=False) # Details of expertise, background
    teaching_style = Column(String, nullable=False) # e.g. 'fun', 'academic', 'friendly'
    price_per_hour = Column(Integer, nullable=False)
    availability = Column(String, nullable=False) # e.g. 'weekdays', 'weekends', 'flexible'
    
    # New Contact and Appointment fields
    contact_line = Column(String, nullable=True) # LINE ID for contact
    contact_phone = Column(String, nullable=True) # Phone number for contact
    contact_email = Column(String, nullable=True) # Email for contact
    appointment_instruction = Column(Text, nullable=True) # Instructions on how to schedule lessons
    
    profile_vector_text = Column(Text, nullable=True) # Formatted text for search index
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    user = relationship("User", back_populates="tutor_profile")

class Appointment(Base):
    __tablename__ = "appointments"

    id = Column(Integer, primary_key=True, index=True)
    student_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"))
    tutor_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"))
    appointment_date = Column(String, nullable=False) # Format: YYYY-MM-DD
    appointment_time = Column(String, nullable=False) # e.g. "13:00 - 15:00"
    notes = Column(Text, nullable=True) # Learning topics or special notes
    status = Column(String, default="pending") # 'pending', 'approved', 'declined'
    created_at = Column(DateTime, default=datetime.utcnow)

    # Relationships to access user data
    student = relationship("User", foreign_keys=[student_id])
    tutor = relationship("User", foreign_keys=[tutor_id])
