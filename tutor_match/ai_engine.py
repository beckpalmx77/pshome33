import cv2
import numpy as np
import base64
import os
import math
from pythainlp.tokenize import word_tokenize

# Load face cascade
cascade_path = os.path.join(cv2.data.haarcascades, "haarcascade_frontalface_default.xml")
face_cascade = cv2.CascadeClassifier(cascade_path)

def extract_face_landmarks(image_bytes: bytes):
    """
    Detects face, crops it, converts to grayscale, resizes to 150x150,
    and returns the base64 string of the cropped face image.
    Returns None if no face is detected.
    """
    nparr = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if img is None:
        return None
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(50, 50))
    
    if len(faces) == 0:
        return None
        
    # Get the largest face
    faces = sorted(faces, key=lambda x: x[2] * x[3], reverse=True)
    x, y, w, h = faces[0]
    
    # Crop and resize
    face_crop = gray[y:y+h, x:x+w]
    face_resized = cv2.resize(face_crop, (150, 150))
    
    # Encode as JPEG base64
    _, buffer = cv2.imencode('.jpg', face_resized)
    val = base64.b64encode(buffer).decode('utf-8')
    return val

def verify_faces(registered_face_b64: str, current_image_bytes: bytes, threshold: float = 75.0):
    """
    Compares registered face with the face from the current image.
    Uses LBPH Face Recognizer.
    Returns: (verified: bool, distance: float, error_message: str)
    """
    # Detect and extract current face
    nparr = np.frombuffer(current_image_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    if img is None:
        return False, 999.0, "ไม่สามารถอ่านรูปภาพได้"
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(50, 50))
    
    if len(faces) == 0:
        return False, 999.0, "ไม่สามารถตรวจจับใบหน้าในรูปภาพได้ กรุณาถ่ายภาพหน้าตรงให้ชัดเจน"
        
    faces = sorted(faces, key=lambda x: x[2] * x[3], reverse=True)
    x, y, w, h = faces[0]
    current_face_resized = cv2.resize(gray[y:y+h, x:x+w], (150, 150))
    
    # Decode registered face
    try:
        reg_data = base64.b64decode(registered_face_b64)
        reg_nparr = np.frombuffer(reg_data, np.uint8)
        registered_face = cv2.imdecode(reg_nparr, cv2.IMREAD_GRAYSCALE)
    except Exception as e:
        return False, 999.0, f"เกิดข้อผิดพลาดในการโหลดข้อมูลใบหน้าที่ลงทะเบียนไว้: {str(e)}"
        
    if registered_face is None:
        return False, 999.0, "ข้อมูลรูปภาพใบหน้าเดิมมีข้อผิดพลาด"
        
    # Create LBPH Face Recognizer and train it on the registered face
    try:
        recognizer = cv2.face.LBPHFaceRecognizer_create()
        recognizer.train([registered_face], np.array([1]))
        label, confidence = recognizer.predict(current_face_resized)
    except Exception as e:
        return False, 999.0, f"โมเดลประมวลผลใบหน้าทำงานผิดพลาด: {str(e)}"
        
    # confidence is the distance. Lower is better.
    # label will always be 1 since we only trained on 1 subject.
    verified = confidence < threshold
    
    return bool(verified), float(confidence), ""


# --- AI Tutor Matching Engine (NLP based on TF-IDF + Thai Word Tokenization) ---
def compute_tf(tokens):
    tf = {}
    for token in tokens:
        tf[token] = tf.get(token, 0) + 1
    total = len(tokens)
    for token in tf:
        tf[token] = tf[token] / total
    return tf

def compute_idf(docs):
    idf = {}
    total_docs = len(docs)
    for doc in docs:
        unique_tokens = set(doc)
        for token in unique_tokens:
            idf[token] = idf.get(token, 0) + 1
    for token in idf:
        idf[token] = math.log(total_docs / idf[token]) + 1
    return idf

def calculate_cosine_similarity(vec1, vec2):
    # Intersection of keys
    keys = set(vec1.keys()) & set(vec2.keys())
    numerator = sum(vec1[k] * vec2[k] for k in keys)
    
    sum1 = sum(v ** 2 for v in vec1.values())
    sum2 = sum(v ** 2 for v in vec2.values())
    
    denominator = math.sqrt(sum1) * math.sqrt(sum2)
    
    if not denominator:
        return 0.0
    return numerator / denominator

def match_tutors(student_req: str, tutors: list, budget: int = None, subject_filter: str = None):
    """
    Matches student requirements with list of tutors.
    tutors list elements must be dictionaries containing:
        {
            'user_id': int,
            'name': str,
            'subjects': str,
            'expertise': str,
            'teaching_style': str,
            'price_per_hour': int,
            'availability': str,
            'profile_text': str
        }
    Returns: List of tutors with match scores, sorted by score descending.
    """
    if not tutors:
        return []
        
    # Tokenize student requirement
    student_tokens = [t for t in word_tokenize(student_req.lower()) if t.strip()]
    
    # Prepare tutor profile documents
    # A tutor document is a combination of subjects + expertise + style
    tutor_docs = []
    for t in tutors:
        # Combine fields into a unified matching text
        text = f"{t['subjects']} {t['expertise']} {t['teaching_style']} {t['availability']}".lower()
        tutor_tokens = [tok for tok in word_tokenize(text) if tok.strip()]
        tutor_docs.append(tutor_tokens)
        
    # Build IDF vocabulary using all tutor profiles + student query as background
    all_docs = tutor_docs + [student_tokens]
    idf = compute_idf(all_docs)
    
    # Calculate TF-IDF vectors
    def get_tfidf_vector(tokens):
        tf = compute_tf(tokens)
        vector = {}
        for token, tf_val in tf.items():
            if token in idf:
                vector[token] = tf_val * idf[token]
        return vector
        
    student_vec = get_tfidf_vector(student_tokens)
    
    matched_results = []
    for idx, t in enumerate(tutors):
        # Apply Hard Filters first
        # 1. Budget filter (if specified, tutor price must be <= budget)
        if budget is not None and t['price_per_hour'] > budget:
            continue
            
        # 2. Subject filter (if student specified a main subject, check if it matches tutor's subjects list)
        if subject_filter:
            sub_filt = subject_filter.strip().lower()
            tutor_subs = [s.strip().lower() for s in t['subjects'].split(',')]
            # If subject filter is not in tutor subjects list, skip or lower score
            # Let's check for substring match
            matched_sub = False
            for s in tutor_subs:
                if sub_filt in s or s in sub_filt:
                    matched_sub = True
                    break
            if not matched_sub:
                continue # Hard filter out if subject doesn't match at all
                
        # Calculate TF-IDF Cosine Similarity
        tutor_vec = get_tfidf_vector(tutor_docs[idx])
        sim_score = calculate_cosine_similarity(student_vec, tutor_vec)
        
        # Boost score slightly if subject matches directly
        if subject_filter:
            sub_filt = subject_filter.strip().lower()
            if sub_filt in t['subjects'].lower():
                sim_score += 0.1 # Boost score
                
        # Cap matching percentage
        match_percentage = min(100.0, max(0.0, sim_score * 100))
        
        # If there's no similarity score (e.g. student query consists of stop words),
        # but the tutor matched hard filters, we give a baseline score (e.g. 50%)
        if match_percentage == 0.0:
            match_percentage = 50.0
            
        # Round score
        match_percentage = round(match_percentage, 1)
        
        # Append result
        t_result = t.copy()
        t_result['match_score'] = match_percentage
        matched_results.append(t_result)
        
    # Sort results by match_score descending
    matched_results.sort(key=lambda x: x['match_score'], reverse=True)
    return matched_results
