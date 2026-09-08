import os
import sys
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

# --- Color Palette ---
COLOR_BG_DARK = RGBColor(15, 23, 42)       # Slate 900
COLOR_BG_LIGHT = RGBColor(248, 250, 252)   # Slate 50
COLOR_CARD_BG = RGBColor(255, 255, 255)    # White
COLOR_CARD_BORDER = RGBColor(226, 232, 240)# Slate 200

COLOR_PRIMARY = RGBColor(124, 58, 237)     # Violet 600
COLOR_PRIMARY_LIGHT = RGBColor(245, 243, 255) # Violet 50
COLOR_INDIGO = RGBColor(79, 70, 229)       # Indigo 600
COLOR_ACCENT = RGBColor(37, 99, 235)       # Blue 600
COLOR_ACCENT_LIGHT = RGBColor(239, 246, 255) # Blue 50
COLOR_SECONDARY = RGBColor(5, 150, 105)    # Emerald 600
COLOR_SECONDARY_LIGHT = RGBColor(236, 253, 245) # Emerald 50
COLOR_WARNING = RGBColor(217, 119, 6)      # Amber 600
COLOR_WARNING_LIGHT = RGBColor(255, 251, 235) # Amber 50
COLOR_DANGER = RGBColor(225, 29, 72)       # Rose 600
COLOR_LINE_GREEN = RGBColor(6, 199, 85)    # LINE Green

COLOR_TEXT_MAIN = RGBColor(15, 23, 42)     # Slate 900
COLOR_TEXT_MUTED = RGBColor(71, 85, 105)   # Slate 600
COLOR_TEXT_LIGHT = RGBColor(255, 255, 255) # White
COLOR_TEXT_SUBTLE = RGBColor(148, 163, 184)# Slate 400

FONT_MAIN = "Sarabun"
FONT_BOLD = "Sarabun"

def create_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6] # Blank slide

    def add_header(slide, category_text, title_text, subtitle_text=""):
        # Background fill
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
        bg.fill.solid()
        bg.fill.fore_color.rgb = COLOR_BG_LIGHT
        bg.line.fill.background()

        # Top Accent Stripe
        stripe = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(0.1))
        stripe.fill.solid()
        stripe.fill.fore_color.rgb = COLOR_PRIMARY
        stripe.line.fill.background()

        # Category Badge
        badge = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(0.4), Inches(3.2), Inches(0.35))
        badge.fill.solid()
        badge.fill.fore_color.rgb = COLOR_PRIMARY_LIGHT
        badge.line.color.rgb = COLOR_PRIMARY
        badge.line.width = Pt(1)
        tf = badge.text_frame
        tf.word_wrap = True
        tf.vertical_anchor = MSO_ANCHOR.MIDDLE
        p = tf.paragraphs[0]
        p.text = category_text.upper()
        p.font.name = FONT_BOLD
        p.font.size = Pt(10)
        p.font.bold = True
        p.font.color.rgb = COLOR_PRIMARY
        p.alignment = PP_ALIGN.CENTER

        # Title
        tx_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.8), Inches(11.7), Inches(0.7))
        tf = tx_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        p = tf.paragraphs[0]
        p.text = title_text
        p.font.name = FONT_BOLD
        p.font.size = Pt(22)
        p.font.bold = True
        p.font.color.rgb = COLOR_TEXT_MAIN

        # Subtitle
        if subtitle_text:
            p2 = tf.add_paragraph()
            p2.text = subtitle_text
            p2.font.name = FONT_MAIN
            p2.font.size = Pt(12)
            p2.font.color.rgb = COLOR_TEXT_MUTED

        # Footer
        ft_box = slide.shapes.add_textbox(Inches(0.8), Inches(7.05), Inches(11.7), Inches(0.3))
        ft_tf = ft_box.text_frame
        ft_p = ft_tf.paragraphs[0]
        ft_p.text = "TutorMatch AI  |  การวิเคราะห์การใช้งานและสถาปัตยกรรมเชิงวัตถุ (Use Cases & UML Specification)"
        ft_p.font.name = FONT_MAIN
        ft_p.font.size = Pt(9)
        ft_p.font.color.rgb = COLOR_TEXT_SUBTLE

    def add_card(slide, left, top, width, height, title, body_bullets, accent_color=COLOR_PRIMARY, icon=""):
        # Card container
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        card.fill.solid()
        card.fill.fore_color.rgb = COLOR_CARD_BG
        card.line.color.rgb = COLOR_CARD_BORDER
        card.line.width = Pt(1)

        # Accent Top Border Strip
        strip = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, Inches(0.08))
        strip.fill.solid()
        strip.fill.fore_color.rgb = accent_color
        strip.line.fill.background()

        # Text Frame
        tb = slide.shapes.add_textbox(left + Inches(0.22), top + Inches(0.18), width - Inches(0.44), height - Inches(0.32))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0

        # Title
        p_title = tf.paragraphs[0]
        display_title = f"{icon}  {title}" if icon else title
        p_title.text = display_title
        p_title.font.name = FONT_BOLD
        p_title.font.size = Pt(14)
        p_title.font.bold = True
        p_title.font.color.rgb = accent_color
        p_title.space_after = Pt(6)

        # Bullets
        for b in body_bullets:
            p_b = tf.add_paragraph()
            p_b.font.name = FONT_MAIN
            p_b.font.size = Pt(11)
            p_b.font.color.rgb = COLOR_TEXT_MUTED
            p_b.space_after = Pt(5)
            
            if isinstance(b, tuple):
                # (highlight, normal_text)
                r1 = p_b.add_run()
                r1.text = "• " + b[0] + ": "
                r1.font.bold = True
                r1.font.color.rgb = COLOR_TEXT_MAIN
                r2 = p_b.add_run()
                r2.text = b[1]
            else:
                p_b.text = "• " + b

    # ==========================================
    # SLIDE 1: TITLE SLIDE (Dark Luxury / Tech)
    # ==========================================
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = COLOR_BG_DARK
    bg1.line.fill.background()

    # Glowing Accent Shapes
    acc1 = s1.shapes.add_shape(MSO_SHAPE.OVAL, Inches(9.5), Inches(-1.5), Inches(5.5), Inches(5.5))
    acc1.fill.solid()
    acc1.fill.fore_color.rgb = RGBColor(30, 27, 75)
    acc1.line.fill.background()

    acc2 = s1.shapes.add_shape(MSO_SHAPE.OVAL, Inches(-1.5), Inches(4.5), Inches(5.0), Inches(5.0))
    acc2.fill.solid()
    acc2.fill.fore_color.rgb = RGBColor(19, 78, 74)
    acc2.line.fill.background()

    # Title Box
    tbox = s1.shapes.add_textbox(Inches(1.2), Inches(1.5), Inches(11.0), Inches(3.2))
    tf = tbox.text_frame
    tf.word_wrap = True

    p_badge = tf.paragraphs[0]
    p_badge.text = "SYSTEM ANALYSIS & UML SPECIFICATION  •  TUTORMATCH AI"
    p_badge.font.name = FONT_BOLD
    p_badge.font.size = Pt(11)
    p_badge.font.bold = True
    p_badge.font.color.rgb = RGBColor(236, 72, 153)
    p_badge.space_after = Pt(14)

    p_main = tf.add_paragraph()
    p_main.text = "Use Cases & UML Diagrams"
    p_main.font.name = FONT_BOLD
    p_main.font.size = Pt(44)
    p_main.font.bold = True
    p_main.font.color.rgb = COLOR_TEXT_LIGHT
    p_main.space_after = Pt(8)

    p_sub = tf.add_paragraph()
    p_sub.text = "การวิเคราะห์ยูสเคสและแบบจำลองเชิงวัตถุของระบบ TutorMatch AI"
    p_sub.font.name = FONT_BOLD
    p_sub.font.size = Pt(22)
    p_sub.font.color.rgb = RGBColor(167, 139, 250)
    p_sub.space_after = Pt(12)

    p_desc = tf.add_paragraph()
    p_desc.text = "การออกแบบเชิงระบบมาตรฐาน UML 2.5: Actors • Use Case Diagram • Class Diagram • Sequence Diagrams • Activity Flow • State Machine • Deployment Architecture"
    p_desc.font.name = FONT_MAIN
    p_desc.font.size = Pt(13)
    p_desc.font.color.rgb = RGBColor(203, 213, 225)

    # Feature Badges Cards (Bottom)
    features = [
        ("📊 Use Case Model", "9 รายการ Use Cases ครบวงจรทั้ง Student, Tutor และ Admin"),
        ("🏛️ Class Diagram", "โครงสร้าง ORM Entity และความสัมพันธ์เชิงวัตถุอย่างเป็นระบบ"),
        ("⏱️ Sequence Flows", "แผนภาพลำดับเวลาการสแกนใบหน้า จับคู่ AI และการนัดหมาย"),
        ("📦 Deployment Topology", "สถาปัตยกรรมการติดตั้งบน LINE LIFF, FastAPI และ Storage")
    ]
    card_w = Inches(2.6)
    card_gap = Inches(0.2)
    start_x = Inches(1.2)
    for i, (f_title, f_desc) in enumerate(features):
        x = start_x + i * (card_w + card_gap)
        c = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x, Inches(5.0), card_w, Inches(1.5))
        c.fill.solid()
        c.fill.fore_color.rgb = RGBColor(30, 41, 59)
        c.line.color.rgb = RGBColor(51, 65, 85)
        c.line.width = Pt(1)

        c_tf = c.text_frame
        c_tf.word_wrap = True
        c_tf.margin_left = c_tf.margin_right = c_tf.margin_top = c_tf.margin_bottom = Inches(0.15)
        
        cp1 = c_tf.paragraphs[0]
        cp1.text = f_title
        cp1.font.name = FONT_BOLD
        cp1.font.size = Pt(12)
        cp1.font.bold = True
        cp1.font.color.rgb = RGBColor(241, 245, 249)
        cp1.space_after = Pt(4)

        cp2 = c_tf.add_paragraph()
        cp2.text = f_desc
        cp2.font.name = FONT_MAIN
        cp2.font.size = Pt(10)
        cp2.font.color.rgb = RGBColor(148, 163, 184)

    # ==========================================
    # SLIDE 2: ACTORS & STAKEHOLDERS
    # ==========================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "1. ผู้กระทำในระบบ", "บทบาทและหน้าที่ของผู้กระทำ (System Actors Specification)", "ระบุผู้มีส่วนได้ส่วนเสีย ผู้ใช้งานตรง และระบบภายนอกที่มีปฏิสัมพันธ์กับระบบ")

    add_card(s2, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ผู้เรียน / ผู้ปกครอง (Student)", 
             [
                 ("ประเภท", "Primary Human Actor"),
                 ("การเข้าถึง", "ผ่าน LINE Official Account และ LINE LIFF"),
                 ("หน้าที่หลัก", "สมัครสมาชิก, สแกนใบหน้ายืนยันตัวตนชีวมิติ, พิมพ์ค้นหาติวเตอร์ด้วยภาษาไทยอิสระ, กรองงบประมาณและวิชา, ดูผลลัพธ์คะแนน AI Match %, และส่งคำขอนัดหมายเวลาเรียน"),
                 ("จุดประสงค์", "ค้นหาผู้สอนที่ตรงกับระดับความรู้ สไตล์การเรียน และงบประมาณอย่างรวดเร็วและปลอดภัย")
             ], 
             accent_color=COLOR_ACCENT, icon="🧑‍🎓")

    add_card(s2, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ผู้สอน / ติวเตอร์ (Tutor)", 
             [
                 ("ประเภท", "Primary Human Actor"),
                 ("การเข้าถึง", "ผ่าน LINE LIFF บนอุปกรณ์มือถือหรือคอมพิวเตอร์"),
                 ("หน้าที่หลัก", "สมัครสมาชิก, สแกนใบหน้าบันทึก Biometric Profile, กรอกประวัติการศึกษา วิชาที่สอน ความเชี่ยวชาญ สไตล์การสอน อัตราค่าบริการ ช่องทาง LINE และพิจารณาอนุมัติคำขอนัดหมาย"),
                 ("จุดประสงค์", "โปรโมตโปรไฟล์ สร้างความน่าเชื่อถือ และรับนักเรียนที่ตรงกับความถนัดอย่างเป็นระบบ")
             ], 
             accent_color=COLOR_PRIMARY, icon="👨‍🏫")

    add_card(s2, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ระบบสนับสนุนและภายนอก", 
             [
                 ("ผู้ดูแลระบบ (Admin)", "จัดการระบบ ตรวจสอบความถูกต้อง และรันคำสั่งจำลองบัญชีติวเตอร์ตัวอย่าง (Mock Data Seed)"),
                 ("AI Engine Subsystem", "โมดูล Haar Cascade + LBPH สำหรับตรวจสอบใบหน้า และ PyThaiNLP + TF-IDF สำหรับวิเคราะห์ภาษา"),
                 ("LINE Platform", "LINE Messaging API ส่งข้อความ Flex Message และ LINE LIFF ให้บริการเว็บแอปบนแชท")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="🤖")

    # ==========================================
    # SLIDE 3: USE CASE DIAGRAM OVERVIEW
    # ==========================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "2. แผนภาพยูสเคส", "แผนภาพยูสเคสหลักของระบบ (Overall Use Case Diagram)", "แสดงขอบเขตของระบบ (System Boundary) ระหว่าง Actor ทั้ง 3 กลุ่ม และความสัมพันธ์กับ Use Cases ทั้ง 9 รายการ")

    # Flow Container visual boxes
    # Left Actor Box
    act_box = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.8), Inches(2.8), Inches(4.8))
    act_box.fill.solid()
    act_box.fill.fore_color.rgb = COLOR_CARD_BG
    act_box.line.color.rgb = COLOR_CARD_BORDER
    act_box.line.width = Pt(1)
    
    atf = act_box.text_frame
    atf.word_wrap = True
    atf.margin_top = atf.margin_left = atf.margin_right = Inches(0.2)
    ap1 = atf.paragraphs[0]
    ap1.text = "👥 ผู้กระทำ (Actors)"
    ap1.font.name = FONT_BOLD
    ap1.font.size = Pt(14)
    ap1.font.bold = True
    ap1.font.color.rgb = COLOR_PRIMARY
    ap1.space_after = Pt(12)

    actor_items = [
        ("🧑‍🎓 ผู้เรียน (Student)", "ค้นหาติวเตอร์, สแกนใบหน้า, จองเวลาเรียน, แชท LINE"),
        ("👨‍🏫 ผู้สอน (Tutor)", "จัดการโปรไฟล์, สแกนใบหน้า, อนุมัตินัดหมาย, แชท LINE"),
        ("🛡️ ผู้ดูแลระบบ (Admin)", "ดูแลระบบ, จำลองติวเตอร์ 5 สาขา")
    ]
    for a_name, a_desc in actor_items:
        ap = atf.add_paragraph()
        ap.text = a_name
        ap.font.name = FONT_BOLD
        ap.font.size = Pt(11.5)
        ap.font.bold = True
        ap.font.color.rgb = COLOR_TEXT_MAIN
        
        ap_sub = atf.add_paragraph()
        ap_sub.text = a_desc
        ap_sub.font.name = FONT_MAIN
        ap_sub.font.size = Pt(9.5)
        ap_sub.font.color.rgb = COLOR_TEXT_MUTED
        ap_sub.space_after = Pt(10)

    # Center Use Cases Grid (9 Use Cases)
    center_w = Inches(5.6)
    uc_box = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(3.85), Inches(1.8), center_w, Inches(4.8))
    uc_box.fill.solid()
    uc_box.fill.fore_color.rgb = COLOR_CARD_BG
    uc_box.line.color.rgb = COLOR_PRIMARY
    uc_box.line.width = Pt(1.5)

    utf = uc_box.text_frame
    utf.word_wrap = True
    utf.margin_top = utf.margin_left = utf.margin_right = Inches(0.2)
    up1 = utf.paragraphs[0]
    up1.text = "🎯 ระบบหลัก: TutorMatch AI System Boundary"
    up1.font.name = FONT_BOLD
    up1.font.size = Pt(14)
    up1.font.bold = True
    up1.font.color.rgb = COLOR_PRIMARY
    up1.space_after = Pt(8)

    ucs = [
        "UC-01: สมัครสมาชิกและกำหนดบทบาท (Register)",
        "UC-02: สแกนและลงทะเบียนใบหน้าชีวมิติ (Face Enrollment)",
        "UC-03: ล็อกอินยืนยันตัวตนด้วยใบหน้า (Face Login)",
        "UC-04: บันทึกข้อมูลประวัติและสไตล์การสอน (Tutor Profile)",
        "UC-05: ค้นหาและจับคู่ติวเตอร์ด้วย AI (Semantic Match)",
        "UC-06: จองนัดหมายเวลาเรียน (Appointment Booking)",
        "UC-07: จัดการคำขอนัดหมาย อนุมัติ/ปฏิเสธ (Appointment Status)",
        "UC-08: ติดต่อสื่อสารตรงผ่าน LINE Official Account",
        "UC-09: จำลองข้อมูลติวเตอร์ตัวอย่าง (Mock Data Seed)"
    ]
    for uc in ucs:
        p = utf.add_paragraph()
        p.text = "• " + uc
        p.font.name = FONT_MAIN
        p.font.size = Pt(10)
        p.font.color.rgb = COLOR_TEXT_MAIN
        p.space_after = Pt(4)

    # Right External Services Box
    ext_box = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(9.7), Inches(1.8), Inches(2.8), Inches(4.8))
    ext_box.fill.solid()
    ext_box.fill.fore_color.rgb = COLOR_CARD_BG
    ext_box.line.color.rgb = COLOR_CARD_BORDER
    ext_box.line.width = Pt(1)

    etf = ext_box.text_frame
    etf.word_wrap = True
    etf.margin_top = etf.margin_left = etf.margin_right = Inches(0.2)
    ep1 = etf.paragraphs[0]
    ep1.text = "⚙️ บริการภายนอก / AI"
    ep1.font.name = FONT_BOLD
    ep1.font.size = Pt(14)
    ep1.font.bold = True
    ep1.font.color.rgb = COLOR_SECONDARY
    ep1.space_after = Pt(12)

    ext_items = [
        ("📸 AI Face Recognizer", "<<include>> ใน UC-02, UC-03 ตรวจสอบพิกัดใบหน้าชีวมิติ"),
        ("🧠 AI Matching Engine", "<<include>> ใน UC-05 ตัดคำไทยและคำนวณ TF-IDF Cosine Sim"),
        ("💬 LINE Platform", "<<include>> ใน UC-08 ส่งข้อความ Flex Message และเปิด LIFF")
    ]
    for e_name, e_desc in ext_items:
        ep = etf.add_paragraph()
        ep.text = e_name
        ep.font.name = FONT_BOLD
        ep.font.size = Pt(11.5)
        ep.font.bold = True
        ep.font.color.rgb = COLOR_TEXT_MAIN
        
        ep_sub = etf.add_paragraph()
        ep_sub.text = e_desc
        ep_sub.font.name = FONT_MAIN
        ep_sub.font.size = Pt(9.5)
        ep_sub.font.color.rgb = COLOR_TEXT_MUTED
        ep_sub.space_after = Pt(10)

    # ==========================================
    # SLIDE 4: USE CASE SPECS - GROUP 1
    # ==========================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "3. ข้อกำหนดการใช้งาน (กลุ่มที่ 1)", "Use Case Specifications: การยืนยันตัวตนชีวมิติ (Auth & Biometrics)", "ข้อกำหนดและขั้นตอนการทำงานอย่างเป็นทางการของระบบลงทะเบียนและเข้าใช้งานด้วยใบหน้า")

    add_card(s4, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-01: สมัครสมาชิกและเลือกบทบาท", 
             [
                 ("Actors", "ผู้เรียน (Student), ติวเตอร์ (Tutor)"),
                 ("Pre-condition", "ผู้ใช้เปิดหน้า LINE LIFF Portal"),
                 ("Main Flow", "1. ผู้ใช้กรอก Username (อังกฤษ) และชื่อจริง\n2. เลือกบทบาทเป็น 'student' หรือ 'tutor'\n3. ระบบตรวจสอบความซ้ำซ้อนของชื่อผู้ใช้\n4. บันทึกบัญชีใหม่ลงตาราง User"),
                 ("Post-condition", "ได้ User ID ในระบบ และเข้าสู่ขั้นตอนสแกนใบหน้า")
             ], 
             accent_color=COLOR_ACCENT, icon="📝")

    add_card(s4, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-02: สแกนลงทะเบียนใบหน้าชีวมิติ", 
             [
                 ("Actors", "ผู้เรียน, ติวเตอร์"),
                 ("Pre-condition", "มีบัญชี User ในระบบ และอนุญาตเปิดกล้อง"),
                 ("Main Flow", "1. ส่องหน้าตรงให้อยู่ในกรอบวงกลมบนหน้าจอ\n2. กดปุ่ม 'บันทึกรูปเพื่อลงทะเบียนใบหน้า'\n3. Haar Cascade ตรวจจับพิกัดใบหน้า\n4. ตัดภาพ 150x150 Grayscale เข้ารหัส Base64\n5. บันทึกเข้าตาราง BiometricProfile"),
                 ("Post-condition", "เทมเพลตใบหน้าพร้อมใช้งานสำหรับล็อกอิน")
             ], 
             accent_color=COLOR_PRIMARY, icon="📸")

    add_card(s4, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-03: ล็อกอินยืนยันตัวตนด้วยใบหน้า", 
             [
                 ("Actors", "ผู้เรียน, ติวเตอร์"),
                 ("Pre-condition", "เคยลงทะเบียนใบหน้าไว้แล้วในระบบ"),
                 ("Main Flow", "1. กรอก Username และเปิดกล้องเว็บแคม\n2. กดปุ่ม 'สแกนและยืนยันตัวตน'\n3. ดึงเทมเพลตเดิมมาเปรียบเทียบด้วย LBPH\n4. คำนวณค่า Distance หาก < 75.0 ถือว่าผ่าน\n5. อนุญาตเข้าสู่หน้าจัดการตาม Role"),
                 ("Exception", "Distance >= 75.0 แจ้งเตือนใบหน้าไม่ตรงกัน")
             ], 
             accent_color=COLOR_SECONDARY, icon="🔑")

    # ==========================================
    # SLIDE 5: USE CASE SPECS - GROUP 2
    # ==========================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "4. ข้อกำหนดการใช้งาน (กลุ่มที่ 2)", "Use Case Specifications: ข้อมูลติวเตอร์และการจับคู่ AI (Profile & Match)", "ข้อกำหนดสำหรับการบันทึกโปรไฟล์ผู้สอน และระบบค้นหาจับคู่ติวเตอร์ด้วยการประมวลผลภาษาธรรมชาติ")

    add_card(s5, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), 
             "UC-04: บันทึกประวัติและสไตล์การสอน (Manage Tutor Profile)", 
             [
                 ("Actors", "ผู้สอน / ติวเตอร์ (Tutor)"),
                 ("Pre-condition", "ยืนยันตัวตนเข้าสู่ระบบในบทบาทติวเตอร์สำเร็จ"),
                 ("ข้อมูลที่บันทึก", "• ประวัติการศึกษา (Education)\n• รายวิชาที่เปิดสอน (Subjects คั่นด้วยจุลภาค เช่น คณิต, ฟิสิกส์)\n• รายละเอียดความเชี่ยวชาญ / ประสบการณ์สอน (Expertise)\n• สไตล์การสอน (Teaching Style เช่น ใจเย็น, เน้นลุยโจทย์)\n• อัตราค่าบริการต่อชั่วโมง (Price per hour) และช่วงเวลาว่าง\n• ช่องทางติดต่อ (LINE ID, เบอร์โทรศัพท์, อีเมล และคำแนะนำนัดหมาย)"),
                 ("Main Flow", "ติวเตอร์กรอกข้อมูลครบถ้วน กดบันทึก ระบบรวมข้อความสร้าง Index Text อัปเดตลงตาราง TutorProfile พร้อมให้ AI ทำการค้นหาจับคู่ทันที")
             ], 
             accent_color=COLOR_INDIGO, icon="👨‍🏫")

    add_card(s5, Inches(6.9), Inches(1.8), Inches(5.6), Inches(4.8), 
             "UC-05: ค้นหาและจับคู่ติวเตอร์ด้วย AI (Semantic Tutor Match)", 
             [
                 ("Actors", "ผู้เรียน / ผู้ปกครอง (Student)"),
                 ("Pre-condition", "เข้าสู่ระบบในบทบาทผู้เรียน"),
                 ("การรับข้อมูล", "• ข้อความความต้องการอิสระภาษาไทย (เช่น 'อยากได้พี่ใจดี สอนฟิสิกส์ ปูพื้นฐาน')\n• ตัวกรองวิชาหลักที่เจาะจง (Subject Filter) และงบประมาณสูงสุด (Budget)"),
                 ("ขั้นตอนประมวลผล AI", "1. Hard Filter: คัดกรองวิชาและงบประมาณต่อชั่วโมง\n2. Thai Tokenizer: ตัดคำภาษาไทยด้วย PyThaiNLP ('newmm')\n3. TF-IDF & Cosine Similarity: คำนวณเวกเตอร์ความคล้ายคลึงเชิงภาษา\n4. Normalization: แปลงเป็นคะแนน Match Score (0 - 100%) พร้อมจัดลำดับติวเตอร์"),
                 ("Post-condition", "แสดงรายการติวเตอร์ที่เหมาะสมที่สุด พร้อมประวัติและช่องทางติดต่อ")
             ], 
             accent_color=COLOR_PRIMARY, icon="🧠")

    # ==========================================
    # SLIDE 6: USE CASE SPECS - GROUP 3
    # ==========================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "5. ข้อกำหนดการใช้งาน (กลุ่มที่ 3)", "Use Case Specifications: การนัดหมายและการติดต่อ (Appointment & Communication)", "ข้อกำหนดสำหรับการจองเวลาเรียน การอนุมัตินัดหมาย และการสื่อสารตรงผ่าน LINE Official Account")

    add_card(s6, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-06: จองนัดหมายเวลาเรียน", 
             [
                 ("Actors", "ผู้เรียน (Student)"),
                 ("Pre-condition", "เลือกติวเตอร์ที่สนใจจากผลลัพธ์การจับคู่ของ AI"),
                 ("Main Flow", "1. ผู้เรียนกดปุ่ม 'นัดหมายเวลาเรียน'\n2. เลือกวันที่เรียน (YYYY-MM-DD)\n3. ระบุช่วงเวลา (เช่น 13:00 - 15:00)\n4. กรอกหัวข้อที่ต้องการเน้นเป็นพิเศษ\n5. ส่งคำขอ ระบบบันทึกลงตาราง Appointment ด้วยสถานะ 'pending'"),
                 ("Post-condition", "สร้างนัดหมายสำเร็จและแสดงในตารางของผู้เรียน")
             ], 
             accent_color=COLOR_ACCENT, icon="📅")

    add_card(s6, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-07: จัดการคำขอนัดหมาย", 
             [
                 ("Actors", "ผู้สอน / ติวเตอร์ (Tutor)"),
                 ("Pre-condition", "มีคำขอนัดหมายใหม่ส่งเข้ามาในระบบ"),
                 ("Main Flow", "1. ติวเตอร์ล็อกอินและดูรายการนัดหมาย\n2. ตรวจสอบ วัน เวลา และหัวข้อที่นักเรียนขอ\n3. กดปุ่ม 'อนุมัติ (Approved)' หรือ 'ปฏิเสธ (Declined)'\n4. ระบบอัปเดตสถานะในฐานข้อมูลแบบ Real-time"),
                 ("Post-condition", "สถานะในแดชบอร์ดของผู้เรียนและผู้สอนเปลี่ยนเป็น Approved")
             ], 
             accent_color=COLOR_SECONDARY, icon="✅")

    add_card(s6, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "UC-08: สื่อสารผ่าน LINE OA", 
             [
                 ("Actors", "ผู้เรียน, ติวเตอร์"),
                 ("Pre-condition", "พบติวเตอร์ที่ถูกใจในระบบ"),
                 ("Main Flow", "1. ผู้เรียนกดปุ่ม 'ทักทาง LINE OA'\n2. ระบบเปิด Deep Link ไปยังหน้าแชท LINE ติวเตอร์ทันที\n3. ผู้เรียนสอบถามรายละเอียดเพิ่มเติม\n4. ติวเตอร์ส่งตัวอย่างเอกสารการสอน แผนการเรียน หรือพิกัดสถานที่เรียนได้โดยตรง"),
                 ("ประโยชน์", "สะดวกรวดเร็ว สอดคล้องกับพฤติกรรมคนไทย")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="💬")

    # ==========================================
    # SLIDE 7: DOMAIN CLASS DIAGRAM
    # ==========================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "6. แผนภาพคลาส", "แผนภาพคลาสเชิงโดเมนและสถาปัตยกรรมระบบ (Domain Class Diagram)", "โครงสร้างคลาสข้อมูล (ORM Entities), เมธอด และความสัมพันธ์เชิงสถาปัตยกรรม")

    classes_data = [
        ("User (เอนทิตีผู้ใช้หลัก)", [
            ("+id: Integer", "Primary Key"),
            ("+username: String", "Unique Index"),
            ("+name: String", "ชื่อ-นามสกุลจริง"),
            ("+role: String", "'student' / 'tutor'"),
            ("+line_id: String", "LINE User ID"),
            ("+registered_at", "DateTime")
        ], COLOR_ACCENT, "👤"),
        ("BiometricProfile (ข้อมูลใบหน้า)", [
            ("+id: Integer", "Primary Key"),
            ("+user_id: Integer", "FK: users.id (1-to-1)"),
            ("+face_landmarks: Text", "Base64 Grayscale Crop"),
            ("+updated_at", "DateTime"),
            ("• ความสัมพันธ์", "One-to-One Cascade กับ User"),
            ("• ฟังก์ชัน", "เปรียบเทียบพิกัด LBPH")
        ], COLOR_PRIMARY, "🔒"),
        ("TutorProfile (ข้อมูลผู้สอน)", [
            ("+id: Integer", "Primary Key"),
            ("+user_id: Integer", "FK: users.id (1-to-1)"),
            ("+subjects, expertise", "Text ข้อมูลการสอน"),
            ("+teaching_style", "สไตล์การสอน"),
            ("+price_per_hour", "เรทราคาต่อชั่วโมง"),
            ("+contact_line, phone", "ช่องทางติดต่อ")
        ], COLOR_INDIGO, "👨‍🏫"),
        ("Appointment (ข้อมูลนัดหมาย)", [
            ("+id: Integer", "Primary Key"),
            ("+student_id: Integer", "FK: users.id"),
            ("+tutor_id: Integer", "FK: users.id"),
            ("+appointment_date", "String: YYYY-MM-DD"),
            ("+appointment_time", "String: ช่วงเวลา"),
            ("+status: String", "'pending'/'approved'")
        ], COLOR_SECONDARY, "📅")
    ]

    cw = Inches(2.7)
    cgap = Inches(0.25)
    for i, (ctitle, cfields, ccolor, cicon) in enumerate(classes_data):
        cx = Inches(0.8) + i * (cw + cgap)
        c = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx, Inches(1.8), cw, Inches(3.6))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Header
        ch = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx, Inches(1.8), cw, Inches(0.55))
        ch.fill.solid()
        ch.fill.fore_color.rgb = ccolor
        ch.line.fill.background()
        chtf = ch.text_frame
        chtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        cp = chtf.paragraphs[0]
        cp.text = f"{cicon} {ctitle}"
        cp.font.name = FONT_BOLD
        cp.font.size = Pt(11)
        cp.font.bold = True
        cp.font.color.rgb = COLOR_TEXT_LIGHT
        cp.alignment = PP_ALIGN.CENTER

        # Fields
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.7)
        ctf.margin_left = ctf.margin_right = Inches(0.12)
        for f_idx, (f_name, f_desc) in enumerate(cfields):
            p = ctf.paragraphs[0] if f_idx == 0 else ctf.add_paragraph()
            r1 = p.add_run()
            r1.text = f_name + " "
            r1.font.bold = True
            r1.font.size = Pt(9.5)
            r1.font.color.rgb = COLOR_TEXT_MAIN
            r2 = p.add_run()
            r2.text = f"({f_desc})"
            r2.font.size = Pt(8.5)
            r2.font.color.rgb = COLOR_TEXT_MUTED
            p.space_after = Pt(3)

    # Bottom Architecture Service Class Box
    srv_box = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(5.6), Inches(11.7), Inches(1.2))
    srv_box.fill.solid()
    srv_box.fill.fore_color.rgb = COLOR_PRIMARY_LIGHT
    srv_box.line.color.rgb = COLOR_PRIMARY
    srv_box.line.width = Pt(1)

    stf = srv_box.text_frame
    stf.word_wrap = True
    stf.margin_left = stf.margin_right = stf.margin_top = stf.margin_bottom = Inches(0.15)
    sp1 = stf.paragraphs[0]
    sp1.text = "⚡ คลาสบริการระดับระบบ (Service & Controller Classes):"
    sp1.font.name = FONT_BOLD
    sp1.font.size = Pt(12)
    sp1.font.bold = True
    sp1.font.color.rgb = COLOR_PRIMARY
    sp1.space_after = Pt(2)

    sp2 = stf.add_paragraph()
    sp2.text = "• AIEngine: extract_face_landmarks(), verify_faces(), compute_tf(), compute_idf(), calculate_cosine_similarity(), match_tutors()\n• FastAPIController: register_user(), register_face(), verify_face(), update_tutor_profile(), match_tutors_api(), create_appointment()"
    sp2.font.name = FONT_MAIN
    sp2.font.size = Pt(10)
    sp2.font.color.rgb = COLOR_TEXT_MAIN

    # ==========================================
    # SLIDE 8: SEQUENCE DIAGRAM 1 - FACE LOGIN
    # ==========================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "7. แผนภาพลำดับการทำงาน (1)", "Sequence Diagram: การยืนยันตัวตนด้วยใบหน้า (Biometric Face Login)", "ขั้นตอนการส่งและเปรียบเทียบข้อมูลภาพถ่ายจากหน้าจอผู้ใช้ ผ่าน API สู่โมเดล LBPH")

    seq1_steps = [
        ("1. ส่องกล้องเว็บแคม", "ผู้ใช้เปิดหน้าเว็บ LIFF เปิดกล้องและกรอก Username", "User ➔ LIFF UI", COLOR_ACCENT),
        ("2. ส่งภาพถ่ายปัจจุบัน", "กดปุ่มสแกน ส่งภาพ Base64 ไปยัง POST /api/biometrics/verify", "LIFF ➔ FastAPI API", COLOR_PRIMARY),
        ("3. ดึงเทมเพลตใบหน้าเดิม", "Query ตาราง User & BiometricProfile ดึงเทมเพลตเดิม", "FastAPI ➔ Database", COLOR_INDIGO),
        ("4. คำนวณความต่าง LBPH", "Haar Cascade ตรวจจับใบหน้า และสกัด Local Binary Patterns คำนวณ Distance", "FastAPI ➔ AI Engine", COLOR_WARNING),
        ("5. ตรวจสอบ Distance Threshold", "หาก Distance < 75.0 ถือว่าผ่าน อนุมัติการเข้าสู่ระบบและสร้าง Session", "AI Engine ➔ User", COLOR_SECONDARY)
    ]

    sq_w = Inches(2.15)
    sq_gap = Inches(0.18)
    for i, (q_title, q_desc, q_flow, q_color) in enumerate(seq1_steps):
        qx = Inches(0.8) + i * (sq_w + sq_gap)
        c = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Header Badge
        hb = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(0.55))
        hb.fill.solid()
        hb.fill.fore_color.rgb = q_color
        hb.line.fill.background()
        hbtf = hb.text_frame
        hbtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        hbp = hbtf.paragraphs[0]
        hbp.text = f"STEP 0{i+1}"
        hbp.font.name = FONT_BOLD
        hbp.font.size = Pt(11)
        hbp.font.bold = True
        hbp.font.color.rgb = COLOR_TEXT_LIGHT
        hbp.alignment = PP_ALIGN.CENTER

        # Content
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.65)
        ctf.margin_left = ctf.margin_right = Inches(0.14)

        p1 = ctf.paragraphs[0]
        p1.text = q_title
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_TEXT_MAIN
        p1.space_after = Pt(4)

        p_flow = ctf.add_paragraph()
        p_flow.text = f"[{q_flow}]"
        p_flow.font.name = FONT_BOLD
        p_flow.font.size = Pt(9.5)
        p_flow.font.color.rgb = q_color
        p_flow.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = q_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 9: SEQUENCE DIAGRAM 2 - AI MATCH
    # ==========================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "8. แผนภาพลำดับการทำงาน (2)", "Sequence Diagram: การค้นหาและจับคู่ติวเตอร์ด้วย AI (Tutor Matching)", "ขั้นตอนการส่งข้อความภาษาไทยอิสระ การตัดคำ และการคำนวณเวกเตอร์ความคล้ายคลึง")

    seq2_steps = [
        ("1. ป้อนความต้องการ", "ผู้เรียนพิมพ์ความต้องการอิสระภาษาไทย กำหนดงบประมาณ และวิชาหลัก", "Student ➔ LIFF UI", COLOR_ACCENT),
        ("2. ส่งคำขอจับคู่", "ส่ง POST /api/match/tutors พร้อม Payload ข้อความความต้องการ", "LIFF ➔ FastAPI", COLOR_PRIMARY),
        ("3. ดึงคลังข้อมูลติวเตอร์", "ดึงรายชื่อติวเตอร์ทั้งหมดที่มีในระบบ เพื่อใช้เป็นพื้นหลังคำนวณ IDF", "FastAPI ➔ Database", COLOR_INDIGO),
        ("4. ประมวลผล NLP & เวกเตอร์", "กรอง Hard Filter ➔ ตัดคำด้วย PyThaiNLP ➔ คำนวณ TF-IDF ➔ Cosine Similarity", "FastAPI ➔ AI Engine", COLOR_WARNING),
        ("5. ส่งผลลัพธ์จัดอันดับ", "คืนค่ารายชื่อติวเตอร์ที่ผ่านเกณฑ์ เรียงตาม Match Score (%) พร้อมสไตล์และปุ่มนัดหมาย", "AI Engine ➔ Student", COLOR_SECONDARY)
    ]

    for i, (q_title, q_desc, q_flow, q_color) in enumerate(seq2_steps):
        qx = Inches(0.8) + i * (sq_w + sq_gap)
        c = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        hb = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(0.55))
        hb.fill.solid()
        hb.fill.fore_color.rgb = q_color
        hb.line.fill.background()
        hbtf = hb.text_frame
        hbtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        hbp = hbtf.paragraphs[0]
        hbp.text = f"STEP 0{i+1}"
        hbp.font.name = FONT_BOLD
        hbp.font.size = Pt(11)
        hbp.font.bold = True
        hbp.font.color.rgb = COLOR_TEXT_LIGHT
        hbp.alignment = PP_ALIGN.CENTER

        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.65)
        ctf.margin_left = ctf.margin_right = Inches(0.14)

        p1 = ctf.paragraphs[0]
        p1.text = q_title
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_TEXT_MAIN
        p1.space_after = Pt(4)

        p_flow = ctf.add_paragraph()
        p_flow.text = f"[{q_flow}]"
        p_flow.font.name = FONT_BOLD
        p_flow.font.size = Pt(9.5)
        p_flow.font.color.rgb = q_color
        p_flow.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = q_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 10: SEQUENCE DIAGRAM 3 - APPOINTMENT
    # ==========================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "9. แผนภาพลำดับการทำงาน (3)", "Sequence Diagram: การจองและการอนุมัตินัดหมาย (Appointment Lifecycle)", "กระบวนการส่งคำขอจองวัน-เวลาเรียน การบันทึกสถานะ Pending และการยืนยัน Approved โดยติวเตอร์")

    seq3_steps = [
        ("1. จองเวลาเรียน", "ผู้เรียนเลือกวันนัดหมาย ช่วงเวลา และระบุหัวข้อที่ต้องการเรียนพิเศษ", "Student ➔ LIFF UI", COLOR_ACCENT),
        ("2. บันทึกคำขอใหม่", "ส่ง POST /api/appointments บันทึกลงตาราง appointments สถานะ 'pending'", "LIFF ➔ FastAPI ➔ DB", COLOR_PRIMARY),
        ("3. แสดงผลคำขอรออนุมัติ", "แดชบอร์ดฝั่งนักเรียนแสดงการ์ดนัดหมายสถานะ '⏳ รอการยืนยัน (Pending)'", "FastAPI ➔ Student UI", COLOR_WARNING),
        ("4. ติวเตอร์พิจารณาคำขอ", "ติวเตอร์เข้าสู่ระบบ ตรวจสอบตาราง และกดปุ่ม 'อนุมัติ (Approved)'", "Tutor ➔ LIFF ➔ API", COLOR_INDIGO),
        ("5. อัปเดตสถานะสมบูรณ์", "ระบบอัปเดต DB และแสดงสถานะ '✅ ยืนยันแล้ว (Approved)' ให้ทั้งสองฝ่ายทราบ", "DB ➔ Both Dashboards", COLOR_SECONDARY)
    ]

    for i, (q_title, q_desc, q_flow, q_color) in enumerate(seq3_steps):
        qx = Inches(0.8) + i * (sq_w + sq_gap)
        c = s10.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        hb = s10.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, qx, Inches(1.8), sq_w, Inches(0.55))
        hb.fill.solid()
        hb.fill.fore_color.rgb = q_color
        hb.line.fill.background()
        hbtf = hb.text_frame
        hbtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        hbp = hbtf.paragraphs[0]
        hbp.text = f"STEP 0{i+1}"
        hbp.font.name = FONT_BOLD
        hbp.font.size = Pt(11)
        hbp.font.bold = True
        hbp.font.color.rgb = COLOR_TEXT_LIGHT
        hbp.alignment = PP_ALIGN.CENTER

        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.65)
        ctf.margin_left = ctf.margin_right = Inches(0.14)

        p1 = ctf.paragraphs[0]
        p1.text = q_title
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_TEXT_MAIN
        p1.space_after = Pt(4)

        p_flow = ctf.add_paragraph()
        p_flow.text = f"[{q_flow}]"
        p_flow.font.name = FONT_BOLD
        p_flow.font.size = Pt(9.5)
        p_flow.font.color.rgb = q_color
        p_flow.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = q_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 11: ACTIVITY DIAGRAM & FLOWCHART
    # ==========================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "10. แผนภาพกิจกรรม", "แผนภาพกิจกรรมการทำงานตลอดกระบวนการ (Activity Diagram & Workflow)", "การไหลของกิจกรรม (Activity Flow) ตั้งแต่เริ่มต้นเข้าสู่ระบบ การตัดสินใจเงื่อนไข จนสิ้นสุดการนัดหมาย")

    add_card(s11, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ระยะที่ 1: การตรวจสอบตัวตน (Auth)", 
             [
                 ("1. เริ่มต้น", "ผู้ใช้เปิดหน้าต่าง LINE LIFF"),
                 ("2. ตรวจสอบการเป็นสมาชิก", "หากยังไม่มีบัญชี ➔ สมัครสมาชิก (กรอก Username, ชื่อ, เลือกบทบาท)"),
                 ("3. ลงทะเบียนใบหน้า", "เปิดกล้อง ➔ ส่องหน้าตรง ➔ บันทึก Base64 เทมเพลตเข้า DB"),
                 ("4. ล็อกอินด้วยสแกนใบหน้า", "ถ่ายภาพสด ➔ ส่ง AI วิเคราะห์ Distance < 75.0"),
                 ("5. เงื่อนไขผลลัพธ์", "หากไม่ผ่านให้ถ่ายใหม่ หากผ่านจะแยก Flow ตามบทบาท (Role Branching)")
             ], 
             accent_color=COLOR_PRIMARY, icon="🔒")

    add_card(s11, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ระยะที่ 2: ฝั่งติวเตอร์ (Tutor Path)", 
             [
                 ("1. บันทึกประวัติ", "กรอกวิชาที่สอน ความถนัด สไตล์ เรทราคา"),
                 ("2. ตั้งค่าการติดต่อ", "กรอก LINE ID, เบอร์โทร และคำแนะนำการจอง"),
                 ("3. สร้าง Search Index", "ระบบบันทึก Vector Text ลง TutorProfile"),
                 ("4. รอรับคำขอนัดหมาย", "ติวเตอร์ตรวจสอบรายการนัดหมายในระบบ"),
                 ("5. ตัดสินใจอนุมัติ", "กดเลือก Approved (ยืนยัน) หรือ Declined (ปฏิเสธ)")
             ], 
             accent_color=COLOR_INDIGO, icon="👨‍🏫")

    add_card(s11, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ระยะที่ 3: ฝั่งผู้เรียน (Student Path)", 
             [
                 ("1. ระบุความต้องการ", "พิมพ์ข้อความภาษาไทยอิสระ เช่น 'อยากได้พี่ใจดี สอนฟิสิกส์'"),
                 ("2. กำหนดตัวกรอง", "เลือกวิชาหลัก และงบประมาณสูงสุด"),
                 ("3. AI คำนวณเวกเตอร์", "ตัดคำไทย ➔ TF-IDF ➔ Cosine Similarity"),
                 ("4. แสดงผล Match Score %", "แสดงการ์ดติวเตอร์เรียงจากมากไปน้อย"),
                 ("5. เลือกวิธีติดต่อ", "• ทักแชท LINE OA ติวเตอร์โดยตรง\n• ส่งคำขอนัดหมายจองคลาสในระบบ")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="🧑‍🎓")

    # ==========================================
    # SLIDE 12: STATE MACHINE DIAGRAM
    # ==========================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "11. แผนภาพสถานะ", "แผนภาพสถานะวงจรชีวิตการนัดหมาย (Appointment State Machine Diagram)", "การเปลี่ยนผ่านของสถานะ (State Transitions) ในระบบนัดหมายเรียนพิเศษ")

    states = [
        ("Pending", "รอการยืนยัน", "สถานะเริ่มต้นเมื่อนักเรียนกดส่งคำขอนัดหมายวัน-เวลาเรียนลงระบบ เพื่อรอให้ติวเตอร์เข้ามาตรวจสอบตารางเวลาว่าง", COLOR_WARNING, "⏳"),
        ("Approved", "ยืนยันการนัดหมาย", "ติวเตอร์ตรวจสอบแล้วเวลาว่างตรงกัน จึงกดยืนยันการสอน ทั้งสองฝ่ายเตรียมเริ่มคลาสเรียนตามวันนัด", COLOR_SECONDARY, "✅"),
        ("Declined", "ปฏิเสธคำขอ", "ติวเตอร์ติดภารกิจอื่น หรือไม่สะดวกสอนในช่วงเวลานั้น ระบบแจ้งให้นักเรียนเลือกวันหรือติวเตอร์ท่านอื่น", COLOR_DANGER, "❌"),
        ("Completed / Cancelled", "สิ้นสุด / ยกเลิก", "คลาสเรียนดำเนินการเสร็จสิ้นตามวันเวลา (Completed) หรือมีการแจ้งขอยกเลิกก่อนเริ่มคลาสเรียน (Cancelled)", COLOR_TEXT_MUTED, "🏁")
    ]

    st_w = Inches(2.7)
    st_gap = Inches(0.25)
    for i, (st_name, st_th, st_desc, st_color, st_icon) in enumerate(states):
        st_x = Inches(0.8) + i * (st_w + st_gap)
        c = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, st_x, Inches(1.8), st_w, Inches(3.6))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = st_color
        c.line.width = Pt(1.5)

        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_left = ctf.margin_right = ctf.margin_top = Inches(0.2)

        p1 = ctf.paragraphs[0]
        p1.text = f"{st_icon} {st_name}"
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(14)
        p1.font.bold = True
        p1.font.color.rgb = st_color
        p1.space_after = Pt(2)

        p_th = ctf.add_paragraph()
        p_th.text = st_th
        p_th.font.name = FONT_BOLD
        p_th.font.size = Pt(11)
        p_th.font.color.rgb = COLOR_TEXT_MAIN
        p_th.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = st_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # Bottom Transition Rules Card
    tr_box = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(5.6), Inches(11.7), Inches(1.2))
    tr_box.fill.solid()
    tr_box.fill.fore_color.rgb = COLOR_CARD_BG
    tr_box.line.color.rgb = COLOR_CARD_BORDER
    tr_box.line.width = Pt(1)

    trtf = tr_box.text_frame
    trtf.word_wrap = True
    trtf.margin_left = trtf.margin_right = trtf.margin_top = trtf.margin_bottom = Inches(0.15)

    tp1 = trtf.paragraphs[0]
    tp1.text = "🔄 กฎการเปลี่ยนสถานะ (State Transition Rules):"
    tp1.font.name = FONT_BOLD
    tp1.font.size = Pt(12)
    tp1.font.bold = True
    tp1.font.color.rgb = COLOR_PRIMARY
    tp1.space_after = Pt(2)

    tp2 = trtf.add_paragraph()
    tp2.text = "• [*] ➔ Pending (Event: create_appointment) | Pending ➔ Approved (Event: tutor_approve) | Pending ➔ Declined (Event: tutor_decline)\n• Approved ➔ Completed (Event: session_end) | Pending / Approved ➔ Cancelled (Event: cancel_appointment)"
    tp2.font.name = FONT_MAIN
    tp2.font.size = Pt(10)
    tp2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 13: DEPLOYMENT & COMPONENT DIAGRAM
    # ==========================================
    s13 = prs.slides.add_slide(blank_layout)
    add_header(s13, "12. สถาปัตยกรรมการติดตั้ง", "แผนภาพการติดตั้งและคอมโพเนนต์ระบบ (Component & Deployment Diagram)", "การจัดวางองค์ประกอบซอฟต์แวร์ ฮาร์ดแวร์ และเครือข่ายคลาวด์สำหรับนำระบบไปใช้งานจริง")

    deploy_nodes = [
        ("Client Tier (อุปกรณ์)", [
            ("Mobile Browser", "LINE App & Safari / Chrome"),
            ("WebRTC Camera", "เปิดสิทธิ์บันทึกภาพสดจากกล้อง"),
            ("LIFF Frontend", "HTML5, CSS3 Modern Flex/Grid")
        ], COLOR_ACCENT, "📱"),
        ("Cloud Gateway Tier", [
            ("LINE LIFF CDN", "โหลด LIFF SDK และ Auth Token"),
            ("Messaging Gateway", "รับส่งข้อความ Flex Messages"),
            ("Reverse Proxy", "Nginx / Cloudflare SSL TLS 1.3")
        ], COLOR_LINE_GREEN, "🌐"),
        ("Application Tier", [
            ("FastAPI Backend", "Python 3.10+ Asynchronous API"),
            ("Uvicorn Server", "High Concurrency ASGI Worker"),
            ("AI Subsystem", "OpenCV, LBPH, PyThaiNLP")
        ], COLOR_PRIMARY, "⚙️"),
        ("Persistence Tier", [
            ("Relational DB", "SQLite / PostgreSQL + ORM"),
            ("Biometrics Store", "Base64 Normalized Face Templates"),
            ("Index Cache", "Tutor Profile Vector Representation")
        ], COLOR_SECONDARY, "💾")
    ]

    for i, (d_name, d_items, d_color, d_icon) in enumerate(deploy_nodes):
        dx = Inches(0.8) + i * (st_w + st_gap)
        c = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, dx, Inches(1.8), st_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Header
        dh = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, dx, Inches(1.8), st_w, Inches(0.55))
        dh.fill.solid()
        dh.fill.fore_color.rgb = d_color
        dh.line.fill.background()
        dhtf = dh.text_frame
        dhtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        dp = dhtf.paragraphs[0]
        dp.text = f"{d_icon} {d_name}"
        dp.font.name = FONT_BOLD
        dp.font.size = Pt(11.5)
        dp.font.bold = True
        dp.font.color.rgb = COLOR_TEXT_LIGHT
        dp.alignment = PP_ALIGN.CENTER

        # Content
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.7)
        ctf.margin_left = ctf.margin_right = Inches(0.14)
        for d_title, d_desc in d_items:
            p = ctf.paragraphs[0] if ctf.paragraphs[0].text == "" else ctf.add_paragraph()
            r1 = p.add_run()
            r1.text = "• " + d_title + "\n"
            r1.font.bold = True
            r1.font.size = Pt(11)
            r1.font.color.rgb = COLOR_TEXT_MAIN
            r2 = p.add_run()
            r2.text = "  " + d_desc
            r2.font.size = Pt(10)
            r2.font.color.rgb = COLOR_TEXT_MUTED
            p.space_after = Pt(8)

    # ==========================================
    # SLIDE 14: CODEBASE MAPPING & TRACEABILITY
    # ==========================================
    s14 = prs.slides.add_slide(blank_layout)
    add_header(s14, "13. การเชื่อมโยงกับซอร์สโค้ด", "ความสอดคล้องระหว่างเอกสาร UML และซอร์สโค้ดจริง (Code Traceability)", "การจับคู่ระหว่างข้อกำหนด Use Cases, UML Classes และไฟล์โค้ดในโครงการ")

    traceability = [
        ("โมเดลฐานข้อมูล (Database Entities)", "models.py", "ตาราง User, BiometricProfile, TutorProfile, Appointment เชื่อมโยงตรงกับ Class Diagram", COLOR_ACCENT, "🗄️"),
        ("โมดูลปัญญาประดิษฐ์ (AI Engine)", "ai_engine.py", "ฟังก์ชัน extract_face_landmarks(), verify_faces(), match_tutors() รองรับ UC-02, UC-03, UC-05", COLOR_PRIMARY, "🧠"),
        ("เว็บคอนโทรลเลอร์ (API Controller)", "main.py", "FastAPI Endpoints รับ-ส่ง Request, ควบคุม Session และการเปลี่ยนสถานะคำขอนัดหมาย", COLOR_INDIGO, "⚡"),
        ("หน้าต่างผู้ใช้ (User Interface)", "static/index.html", "LINE LIFF Web Portal จำลองระบบสแกนใบหน้า ค้นหาติวเตอร์ และแดชบอร์ดนัดหมาย", COLOR_LINE_GREEN, "📱")
    ]

    r_w = Inches(5.6)
    r_h = Inches(2.25)
    for i, (r_title, r_file, r_desc, r_color, r_icon) in enumerate(traceability):
        col = i % 2
        row = i // 2
        rx = Inches(0.8) + col * Inches(6.1)
        ry = Inches(1.8) + row * Inches(2.55)

        card = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, rx, ry, r_w, r_h)
        card.fill.solid()
        card.fill.fore_color.rgb = COLOR_CARD_BG
        card.line.color.rgb = COLOR_CARD_BORDER
        card.line.width = Pt(1)

        r_strip = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, rx, ry, Inches(0.12), r_h)
        r_strip.fill.solid()
        r_strip.fill.fore_color.rgb = r_color
        r_strip.line.fill.background()

        ct = s14.shapes.add_textbox(rx + Inches(0.3), ry + Inches(0.2), r_w - Inches(0.5), r_h - Inches(0.3))
        ctf = ct.text_frame
        ctf.word_wrap = True
        ctf.margin_top = ctf.margin_bottom = ctf.margin_left = ctf.margin_right = 0

        p1 = ctf.paragraphs[0]
        p1.text = f"{r_icon}  {r_title}"
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(13)
        p1.font.bold = True
        p1.font.color.rgb = r_color
        p1.space_after = Pt(2)

        p2 = ctf.add_paragraph()
        p2.text = f"Source File: [{r_file}]"
        p2.font.name = FONT_BOLD
        p2.font.size = Pt(11)
        p2.font.color.rgb = COLOR_TEXT_MAIN
        p2.space_after = Pt(6)

        p3 = ctf.add_paragraph()
        p3.text = r_desc
        p3.font.name = FONT_MAIN
        p3.font.size = Pt(10.5)
        p3.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 15: CONCLUSION & Q&A
    # ==========================================
    s15 = prs.slides.add_slide(blank_layout)
    bg15 = s15.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg15.fill.solid()
    bg15.fill.fore_color.rgb = COLOR_BG_DARK
    bg15.line.fill.background()

    acc15 = s15.shapes.add_shape(MSO_SHAPE.OVAL, Inches(8.5), Inches(1.5), Inches(6.0), Inches(6.0))
    acc15.fill.solid()
    acc15.fill.fore_color.rgb = RGBColor(30, 27, 75)
    acc15.line.fill.background()

    tbox15 = s15.shapes.add_textbox(Inches(1.2), Inches(1.5), Inches(11.0), Inches(4.5))
    tf15 = tbox15.text_frame
    tf15.word_wrap = True

    p_b15 = tf15.paragraphs[0]
    p_b15.text = "UML SPECIFICATION SUMMARY & VERIFICATION"
    p_b15.font.name = FONT_BOLD
    p_b15.font.size = Pt(12)
    p_b15.font.bold = True
    p_b15.font.color.rgb = RGBColor(236, 72, 153)
    p_b15.space_after = Pt(14)

    p_m15 = tf15.add_paragraph()
    p_m15.text = "สรุปผลการวิเคราะห์และออกแบบระบบ"
    p_m15.font.name = FONT_BOLD
    p_m15.font.size = Pt(36)
    p_m15.font.bold = True
    p_m15.font.color.rgb = COLOR_TEXT_LIGHT
    p_m15.space_after = Pt(12)

    p_d15 = tf15.add_paragraph()
    p_d15.text = "การจัดทำ Use Cases และแผนภาพ UML ครบถ้วนตามมาตรฐานวิศวกรรมซอฟต์แวร์ ช่วยให้ทีมพัฒนาและผู้มีส่วนได้ส่วนเสียเห็นภาพรวมทางสถาปัตยกรรมที่ชัดเจน ครอบคลุมทั้ง:\n• แผนภาพโครงสร้าง (Structural Diagrams): Use Case Diagram, Class Diagram, Deployment Diagram\n• แผนภาพพฤติกรรม (Behavioral Diagrams): Sequence Diagrams, Activity Diagram, State Machine Diagram\n\nพร้อมรองรับการพัฒนา ตรวจสอบ และขยายขีดความสามารถของระบบในระดับ Enterprise ต่อไป"
    p_d15.font.name = FONT_MAIN
    p_d15.font.size = Pt(13.5)
    p_d15.font.color.rgb = RGBColor(203, 213, 225)
    p_d15.space_after = Pt(22)

    p_q = tf15.add_paragraph()
    p_q.text = "🎉 ขอขอบพระคุณทุกท่าน  |  เข้าสู่ช่วงถาม-ตอบ (Q&A Session)"
    p_q.font.name = FONT_BOLD
    p_q.font.size = Pt(18)
    p_q.font.bold = True
    p_q.font.color.rgb = RGBColor(167, 139, 250)

    # Save presentation
    output_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "TutorMatch_AI_UseCase_and_UML.pptx")
    prs.save(output_path)
    print(f"Successfully generated Use Case & UML PowerPoint presentation at: {output_path}")

if __name__ == "__main__":
    create_deck()
