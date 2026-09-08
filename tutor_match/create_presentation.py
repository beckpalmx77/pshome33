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
        badge = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(0.4), Inches(2.8), Inches(0.35))
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
        ft_p.text = "TutorMatch AI  |  สถาปัตยกรรมระบบและแนวคิดการออกแบบ (Concept & Architecture)"
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
        tb = slide.shapes.add_textbox(left + Inches(0.25), top + Inches(0.2), width - Inches(0.5), height - Inches(0.35))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0

        # Title
        p_title = tf.paragraphs[0]
        display_title = f"{icon}  {title}" if icon else title
        p_title.text = display_title
        p_title.font.name = FONT_BOLD
        p_title.font.size = Pt(15)
        p_title.font.bold = True
        p_title.font.color.rgb = accent_color
        p_title.space_after = Pt(8)

        # Bullets
        for b in body_bullets:
            p_b = tf.add_paragraph()
            p_b.font.name = FONT_MAIN
            p_b.font.size = Pt(11.5)
            p_b.font.color.rgb = COLOR_TEXT_MUTED
            p_b.space_after = Pt(6)
            
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
    acc1.fill.fore_color.rgb = RGBColor(30, 27, 75) # Dark violet
    acc1.line.fill.background()

    acc2 = s1.shapes.add_shape(MSO_SHAPE.OVAL, Inches(-1.5), Inches(4.5), Inches(5.0), Inches(5.0))
    acc2.fill.solid()
    acc2.fill.fore_color.rgb = RGBColor(19, 78, 74) # Dark emerald
    acc2.line.fill.background()

    # Title Box
    tbox = s1.shapes.add_textbox(Inches(1.2), Inches(1.5), Inches(11.0), Inches(3.2))
    tf = tbox.text_frame
    tf.word_wrap = True

    # Badge
    p_badge = tf.paragraphs[0]
    p_badge.text = "AI-POWERED EDUCATION PLATFORM  •  CONCEPT & ARCHITECTURE"
    p_badge.font.name = FONT_BOLD
    p_badge.font.size = Pt(11)
    p_badge.font.bold = True
    p_badge.font.color.rgb = RGBColor(236, 72, 153) # Pink accent
    p_badge.space_after = Pt(14)

    # Main Title
    p_main = tf.add_paragraph()
    p_main.text = "TutorMatch AI"
    p_main.font.name = FONT_BOLD
    p_main.font.size = Pt(44)
    p_main.font.bold = True
    p_main.font.color.rgb = COLOR_TEXT_LIGHT
    p_main.space_after = Pt(8)

    # Subtitle
    p_sub = tf.add_paragraph()
    p_sub.text = "ระบบจับคู่ติวเตอร์อัจฉริยะด้วยปัญญาประดิษฐ์ ผ่าน LINE Official Account"
    p_sub.font.name = FONT_BOLD
    p_sub.font.size = Pt(22)
    p_sub.font.color.rgb = RGBColor(167, 139, 250) # Light violet
    p_sub.space_after = Pt(12)

    # Description
    p_desc = tf.add_paragraph()
    p_desc.text = "การยืนยันตัวตนด้วยใบหน้าชีวมิติ (Face Biometrics) และการวิเคราะห์การจับคู่เชิงความหมายด้วยภาษาธรรมชาติ (Thai Semantic Matching)"
    p_desc.font.name = FONT_MAIN
    p_desc.font.size = Pt(13)
    p_desc.font.color.rgb = RGBColor(203, 213, 225)

    # Feature Badges Cards (Bottom)
    features = [
        ("📱 LINE Ecosystem", "ใช้งานผ่าน LINE LIFF สะดวกไม่ต้องโหลดแอป"),
        ("👤 Face Recognition", "ยืนยันตัวตนชีวมิติด้วย LBPH & OpenCV ปลอดภัย 100%"),
        ("🧠 Thai NLP Matching", "วิเคราะห์สไตล์และความถนัดด้วย TF-IDF & Cosine Similarity"),
        ("⚡ FastAPI High-Perf", "ประมวลผลรวดเร็วแบบ Asynchronous รองรับการขยายตัว")
    ]
    card_w = Inches(2.6)
    card_gap = Inches(0.2)
    start_x = Inches(1.2)
    for i, (f_title, f_desc) in enumerate(features):
        x = start_x + i * (card_w + card_gap)
        c = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x, Inches(5.0), card_w, Inches(1.5))
        c.fill.solid()
        c.fill.fore_color.rgb = RGBColor(30, 41, 59) # Slate 800
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
    # SLIDE 2: PROBLEM STATEMENT & BACKGROUND
    # ==========================================
    s2 = prs.slides.add_slide(blank_layout)
    add_header(s2, "1. ที่มาและความสำคัญ", "ปัญหาในระบบการค้นหาติวเตอร์แบบดั้งเดิม (Problem Statement)", "ช่องว่างระหว่างความต้องการที่แท้จริงของผู้เรียน กับระบบค้นหาติวเตอร์ทั่วไปในปัจจุบัน")

    add_card(s2, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "การค้นหาแบบเดิมไม่ตรงใจ", 
             [
                 ("ค้นหาแบบ Keyword หยาบๆ", "ระบบทั่วไปค้นหาแค่ชื่อวิชา แต่ไม่เข้าใจบริบทเชิงลึก เช่น ระดับความรู้พื้นฐาน หรือเป้าหมายในการสอบ"),
                 ("ละเลยสไตล์การสอน", "นักเรียนแต่ละคนชอบสไตล์ต่างกัน (เช่น เน้นสนุกสนาน, เน้นลุยโจทย์เข้มข้น, หรือใจเย็นสอนช้าๆ) แต่ระบบเดิมไม่สามารถประเมินได้"),
                 ("เสียเวลาและค่าใช้จ่าย", "ผู้เรียนต้องเสียเวลาทดลองเรียนกับติวเตอร์หลายคนกว่าจะพบคนที่เข้ากับตนเองได้จริง")
             ], 
             accent_color=COLOR_WARNING, icon="⚠️")

    add_card(s2, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ความปลอดภัยและการสวมรอย", 
             [
                 ("ขาดการยืนยันตัวตนที่แท้จริง", "มักสมัครผ่านอีเมลหรือเบอร์โทรธรรมดา ไม่สามารถตรวจสอบใบหน้าของติวเตอร์หรือผู้เรียนจริงได้"),
                 ("ความเสี่ยงต่อผู้เรียน", "การนัดหมายเรียนตัวต่อตัวมีความเสี่ยงด้านความปลอดภัย หากแพลตฟอร์มไม่มีการยืนยันอัตลักษณ์บุคคล"),
                 ("การแอบอ้างโปรไฟล์", "อาจมีการสวมรอยใช้ประวัติการศึกษาของผู้อื่นเพื่อเปิดรับสอนพิเศษ สร้างความเสียหายต่อผู้บริโภค")
             ], 
             accent_color=RGBColor(225, 29, 72), icon="🛡️")

    add_card(s2, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ความยุ่งยากในการใช้งาน", 
             [
                 ("อุปสรรคจากการโหลดแอป", "ผู้ใช้ต้องดาวน์โหลดแอปใหม่ สมัครสมาชิกหลายขั้นตอน และมักลบแอปทิ้งหลังใช้งาน"),
                 ("ขาดความต่อเนื่อง", "ช่องทางการค้นหากับช่องทางการติดต่อสื่อสารแยกส่วนกัน (ต้องสลับไปมาระหว่างแอปกับแชท)"),
                 ("ไม่มีระบบนัดหมายที่เชื่อมโยง", "การจองคลาสเรียนไม่มีการบันทึกสถานะที่เป็นระบบ ทำให้ตกหล่นหรือสื่อสารไม่ตรงกัน")
             ], 
             accent_color=COLOR_ACCENT, icon="📱")

    # ==========================================
    # SLIDE 3: CORE VISION & 3 PILLARS
    # ==========================================
    s3 = prs.slides.add_slide(blank_layout)
    add_header(s3, "2. แนวคิดและวิสัยทัศน์", "3 เสาหลักในการออกแบบระบบ TutorMatch AI (Core Pillars)", "การผสานเทคโนโลยีสมัยใหม่เข้ากับพฤติกรรมผู้ใช้งานจริง เพื่อสร้างแพลตฟอร์มที่สมบูรณ์แบบ")

    add_card(s3, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "1. Seamless Accessibility", 
             [
                 ("LINE Native Experience", "เชื่อมต่อผ่าน LINE Official Account และ LINE LIFF 100% ผู้ใช้เข้าถึงได้ทันทีจากแอปที่เปิดทุกวัน"),
                 ("Zero Installation Friction", "ไม่ต้องดาวน์โหลด ไม่ต้องอัปเดตแอป รองรับทั้ง iOS, Android และ Desktop"),
                 ("Direct Communication", "คุยตรง นัดหมาย และรับการแจ้งเตือนผ่านช่องทาง LINE แชทที่คุ้นเคยได้อย่างไร้รอยต่อ")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="💬")

    add_card(s3, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "2. Biometric Trust & Safety", 
             [
                 ("Face Recognition Login", "ใช้การสแกนใบหน้าจริงผ่านกล้องมือถือ/เว็บบราวเซอร์เพื่อเข้าสู่ระบบ แทนรหัสผ่านธรรมดา"),
                 ("Identity Verification", "ยืนยันตัวตนทั้งฝั่งผู้สอนและผู้เรียน ป้องกันการแอบอ้างสวมรอยโปรไฟล์อย่างเด็ดขาด"),
                 ("Standard Feature Template", "สกัดลักษณะเด่นของใบหน้าเก็บเป็นพิกัดปลอดภัย ไม่ต้องเก็บรหัสผ่านที่อาจรั่วไหล")
             ], 
             accent_color=COLOR_PRIMARY, icon="🔒")

    add_card(s3, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "3. Intelligent Semantic Match", 
             [
                 ("Thai NLP Understanding", "ระบบเข้าใจข้อความภาษาไทยอิสระ เช่น 'อยากได้พี่ใจดี สอนฟิสิกส์ ปูพื้นฐาน'"),
                 ("Two-Stage Filtering", "กรองเบื้องต้นด้วยงบประมาณและวิชาหลัก จากนั้นคำนวณความเข้ากันได้เชิงความหมาย (Semantic Score)"),
                 ("Match Percentage Ranking", "แสดงผลการจับคู่เป็นคะแนนเปอร์เซ็นต์ ช่วยให้ตัดสินใจเลือกติวเตอร์ได้อย่างมั่นใจ")
             ], 
             accent_color=COLOR_INDIGO, icon="🤖")

    # ==========================================
    # SLIDE 4: SYSTEM ARCHITECTURE (5-Tier)
    # ==========================================
    s4 = prs.slides.add_slide(blank_layout)
    add_header(s4, "3. สถาปัตยกรรมระบบ", "โครงสร้างสถาปัตยกรรมระบบ 5 ระดับ (5-Tier System Architecture)", "การแบ่งหน้าที่การทำงานอย่างเป็นเอกเทศ (Separation of Concerns) เพื่อประสิทธิภาพและความเสถียร")

    tiers = [
        ("Presentation Layer", "LINE App & LIFF Interface", "หน้ากากเว็บแอปพลิเคชันฝังใน LINE, ควบคุมกล้อง WebRTC, รองรับ Responsive ทุกอุปกรณ์", COLOR_LINE_GREEN, "📱"),
        ("Gateway Layer", "LINE Messaging & Webhook", "รับส่งข้อความ Push/Reply Notification, Interactive Flex Message Cards, จัดการ Event Webhook", COLOR_ACCENT, "🔗"),
        ("Application / API Layer", "FastAPI Python Backend", "ประมวลผลคำขอ Asynchronous REST APIs, ตรรกะทางธุรกิจ (Business Logic), จัดการ Session และการนัดหมาย", COLOR_PRIMARY, "⚡"),
        ("AI Intelligence Layer", "Computer Vision & Thai NLP", "โมดูล Face Detection & LBPH Verification, PyThaiNLP Tokenizer, TF-IDF Vector & Cosine Similarity", COLOR_INDIGO, "🧠"),
        ("Data Persistence Layer", "Relational & Vector Storage", "SQLAlchemy ORM, SQLite / PostgreSQL, จัดเก็บ User Credentials, Biometrics, Profiles และ Appointments", COLOR_SECONDARY, "💾")
    ]

    tier_h = Inches(0.9)
    tier_gap = Inches(0.12)
    start_y = Inches(1.8)
    for i, (t_name, t_tech, t_desc, t_color, t_icon) in enumerate(tiers):
        y = start_y + i * (tier_h + tier_gap)
        
        # Main Bar
        bar = s4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), y, Inches(11.7), tier_h)
        bar.fill.solid()
        bar.fill.fore_color.rgb = COLOR_CARD_BG
        bar.line.color.rgb = COLOR_CARD_BORDER
        bar.line.width = Pt(1)

        # Left Accent Box
        box = s4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), y, Inches(2.6), tier_h)
        box.fill.solid()
        box.fill.fore_color.rgb = t_color
        box.line.fill.background()
        
        btf = box.text_frame
        btf.word_wrap = True
        btf.vertical_anchor = MSO_ANCHOR.MIDDLE
        bp1 = btf.paragraphs[0]
        bp1.text = f"{t_icon} {t_name}"
        bp1.font.name = FONT_BOLD
        bp1.font.size = Pt(13)
        bp1.font.bold = True
        bp1.font.color.rgb = COLOR_TEXT_LIGHT
        bp1.alignment = PP_ALIGN.CENTER

        # Right Content Box
        rc = s4.shapes.add_textbox(Inches(3.6), y + Inches(0.12), Inches(8.7), tier_h - Inches(0.24))
        rtf = rc.text_frame
        rtf.word_wrap = True
        rtf.margin_top = rtf.margin_bottom = 0
        
        rp1 = rtf.paragraphs[0]
        rp1.text = t_tech
        rp1.font.name = FONT_BOLD
        rp1.font.size = Pt(13)
        rp1.font.bold = True
        rp1.font.color.rgb = t_color

        rp2 = rtf.add_paragraph()
        rp2.text = t_desc
        rp2.font.name = FONT_MAIN
        rp2.font.size = Pt(11)
        rp2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 5: TECHNOLOGY STACK
    # ==========================================
    s5 = prs.slides.add_slide(blank_layout)
    add_header(s5, "4. เทคโนโลยีที่เลือกใช้", "ชุดเทคโนโลยีหลักในการพัฒนาระบบ (Technology Stack)", "คัดเลือกเทคโนโลยีที่มีเสถียรภาพสูง ประมวลผลรวดเร็ว และรองรับการสเกลในอนาคต")

    add_card(s5, Inches(0.8), Inches(1.8), Inches(2.7), Inches(4.8), 
             "Backend & Web API", 
             [
                 ("Python 3.10+", "ภาษาหลักในการคำนวณ AI และระบบหลังบ้าน"),
                 ("FastAPI Framework", "เว็บเฟรมเวิร์ก Asynchronous ความเร็วสูง เอกสาร OpenAPI อัตโนมัติ"),
                 ("Uvicorn ASGI", "Web Server ประสิทธิภาพสูง รองรับ Concurrent Requests"),
                 ("Pydantic & ORM", "ตรวจสอบความถูกต้องของข้อมูล (Validation) และเชื่อมโยง Database")
             ], 
             accent_color=COLOR_ACCENT, icon="🐍")

    add_card(s5, Inches(3.8), Inches(1.8), Inches(2.7), Inches(4.8), 
             "LINE Platform", 
             [
                 ("LINE Messaging API", "เชื่อมต่อการสื่อสาร รับ Webhook Event ส่ง Push Messages"),
                 ("LINE LIFF (Front-end)", "ฝัง Web App บน LINE เปิดกล้องและควบคุม UI ได้อย่างราบรื่น"),
                 ("Flex Messages", "การ์ดข้อความแบบโต้ตอบ (Interactive JSON) สวยงาม น่าอ่าน"),
                 ("LINE Official Account", "จุดศูนย์กลางในการสื่อสารกับผู้ใช้งานในไทย")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="💬")

    add_card(s5, Inches(6.8), Inches(1.8), Inches(2.7), Inches(4.8), 
             "AI & Computer Vision", 
             [
                 ("OpenCV (cv2)", "ประมวลผลรูปภาพขั้นสูง ปรับ Grayscale และ Resize"),
                 ("Haar Cascade", "ตรวจจับพิกัดใบหน้า (Frontal Face Detection) แม่นยำ รวดเร็ว"),
                 ("LBPH Face Recognizer", "สกัดลักษณะเด่นของใบหน้า (Local Binary Patterns) เปรียบเทียบเวกเตอร์"),
                 ("Base64 Encoding", "แปลงข้อมูลภาพระหว่างกล้องและ API แบบ Real-time")
             ], 
             accent_color=COLOR_PRIMARY, icon="📸")

    add_card(s5, Inches(9.8), Inches(1.8), Inches(2.7), Inches(4.8), 
             "NLP & Database", 
             [
                 ("PyThaiNLP ('newmm')", "โมเดลตัดคำภาษาไทยพจนานุกรมความเร็วสูง"),
                 ("TF-IDF Vectorizer", "ประเมินค่าน้ำหนักความสำคัญของคำศัพท์ในโปรไฟล์และคำค้นหา"),
                 ("Cosine Similarity", "คำนวณองศาความคล้ายคลึงของเวกเตอร์ความต้องการ"),
                 ("SQLAlchemy & SQLite", "ฐานข้อมูลสัมพันธ์ เก็บข้อมูลผู้ใช้ ประวัติ และการนัดหมาย")
             ], 
             accent_color=COLOR_INDIGO, icon="🗄️")

    # ==========================================
    # SLIDE 6: AI ENGINE 1 - FACE BIOMETRICS
    # ==========================================
    s6 = prs.slides.add_slide(blank_layout)
    add_header(s6, "5. กลไก AI ส่วนที่ 1", "ระบบยืนยันตัวตนด้วยใบหน้าชีวมิติ (Face Biometric Verification)", "กระบวนการตรวจสอบอัตลักษณ์บุคคลผ่านภาพถ่ายจากกล้องเว็บแคม เพื่อความปลอดภัยสูงสุด")

    steps_face = [
        ("Step 1: Capture & Decode", "รับภาพสดจากกล้อง WebRTC บน LINE LIFF ส่งมายัง API ในรูปแบบ JPEG/Base64 และ Decode เข้าสู่หน่วยความจำ", COLOR_ACCENT, "📷"),
        ("Step 2: Face Detection", "ใช้ Haar Cascade Classifier ตรวจจับพิกัดใบหน้า หากพบหลายใบหน้าจะคัดเลือกเฉพาะใบหน้าที่ใหญ่และใกล้กล้องที่สุด", COLOR_PRIMARY, "🔍"),
        ("Step 3: Normalize & Crop", "ตัดครอบเฉพาะกรอบใบหน้า (Face Crop) ปรับขนาดเป็นมาตรฐาน 150x150 pixels และแปลงเป็นภาพขาวดำ (Grayscale)", COLOR_INDIGO, "✂️"),
        ("Step 4: LBPH Verification", "สกัด Local Binary Patterns Histograms เปรียบเทียบกับเทมเพลตที่ลงทะเบียนไว้ หากค่า Distance < 75.0 ถือว่าผ่าน", COLOR_SECONDARY, "✅")
    ]

    sw = Inches(2.7)
    sgap = Inches(0.25)
    for i, (stitle, sdesc, scolor, sicon) in enumerate(steps_face):
        sx = Inches(0.8) + i * (sw + sgap)
        c = s6.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, sx, Inches(1.8), sw, Inches(3.0))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = scolor
        c.line.width = Pt(1.5)

        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = ctf.margin_bottom = ctf.margin_left = ctf.margin_right = Inches(0.2)

        p1 = ctf.paragraphs[0]
        p1.text = f"{sicon} {stitle}"
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(13)
        p1.font.bold = True
        p1.font.color.rgb = scolor
        p1.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = sdesc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(11)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # Bottom Technical Note Card
    note_box = s6.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(5.1), Inches(11.7), Inches(1.6))
    note_box.fill.solid()
    note_box.fill.fore_color.rgb = COLOR_PRIMARY_LIGHT
    note_box.line.color.rgb = COLOR_PRIMARY
    note_box.line.width = Pt(1)

    ntf = note_box.text_frame
    ntf.word_wrap = True
    ntf.margin_left = ntf.margin_right = ntf.margin_top = ntf.margin_bottom = Inches(0.2)
    
    np1 = ntf.paragraphs[0]
    np1.text = "💡 จุดเด่นทางเทคนิคของโมเดล LBPH (Local Binary Patterns Histograms)"
    np1.font.name = FONT_BOLD
    np1.font.size = Pt(13)
    np1.font.bold = True
    np1.font.color.rgb = COLOR_PRIMARY
    np1.space_after = Pt(4)

    np2 = ntf.add_paragraph()
    np2.text = "• ทนทานต่อการเปลี่ยนแปลงของแสง (Illumination Invariance): การเปรียบเทียบพิกัดแบบ Local Texture ทำให้ทำงานได้ดีแม้สภาพแสงในห้องจะเปลี่ยนแปลง\n• น้ำหนักเบาและประมวลผลเร็ว (Real-time Efficiency): สามารถ Train และ Predict เทมเพลตใบหน้าได้ภายในเสี้ยววินาทีบน Server ทรัพยากรปกติ ไม่จำเป็นต้องใช้ GPU ขนาดใหญ่\n• ความปลอดภัยของข้อมูล (Privacy by Design): ข้อมูลที่บันทึกคือรูปแบบการกระจายพิกเซล ไม่สามารถย้อนกลับมาเป็นภาพถ่ายส่วนตัวของผู้ใช้ได้"
    np2.font.name = FONT_MAIN
    np2.font.size = Pt(10.5)
    np2.font.color.rgb = COLOR_TEXT_MAIN

    # ==========================================
    # SLIDE 7: AI ENGINE 2 - TUTOR MATCHING
    # ==========================================
    s7 = prs.slides.add_slide(blank_layout)
    add_header(s7, "6. กลไก AI ส่วนที่ 2", "ระบบวิเคราะห์จับคู่ติวเตอร์อัจฉริยะ (Semantic Tutor Matching Engine)", "สถาปัตยกรรมการจับคู่แบบ 2 ขั้นตอน (Two-Stage Matching) ที่ผสานเกณฑ์บังคับและการวิเคราะห์เชิงความหมาย")

    add_card(s7, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), 
             "Stage 1: Hard Filtering (เกณฑ์บังคับ)", 
             [
                 ("การคัดกรองรายวิชา (Subject Filter)", "หากผู้เรียนระบุวิชาหลักที่เจาะจง (เช่น 'ฟิสิกส์') ระบบจะคัดกรองเฉพาะติวเตอร์ที่มีรายวิชานี้ในโปรไฟล์เท่านั้น"),
                 ("การจำกัดเพดานงบประมาณ (Budget Constraint)", "คัดกรองติวเตอร์ที่มีอัตราค่าสอนต่อชั่วโมงไม่เกินงบประมาณที่ผู้เรียนระบุ (Price per hour <= Budget)"),
                 ("ผลลัพธ์ของ Stage 1", "ตัดติวเตอร์ที่ไม่ตรงกับเงื่อนไขพื้นฐานออกทันที ลดภาระในการคำนวณ และรับประกันว่าจะได้ติวเตอร์ที่อยู่ในงบประมาณแน่นอน")
             ], 
             accent_color=COLOR_WARNING, icon="⚖️")

    add_card(s7, Inches(6.9), Inches(1.8), Inches(5.6), Inches(4.8), 
             "Stage 2: Soft & Semantic Scoring (คะแนนความเข้ากันได้)", 
             [
                 ("Thai Word Tokenization", "ใช้ PyThaiNLP ตัดคำจากข้อความความต้องการของผู้เรียน และประวัติ ความถนัด สไตล์การสอนของติวเตอร์"),
                 ("TF-IDF Vectorization", "คำนวณความถี่และค่าน้ำหนักความสำคัญของคำศัพท์ (TF) เทียบกับคลังเอกสารทั้งหมด (IDF) เพื่อสร้างเวกเตอร์หลายมิติ"),
                 ("Cosine Similarity Calculation", "คำนวณค่า Cosine ของมุมระหว่างเวกเตอร์ผู้เรียนและติวเตอร์ เพื่อหาความใกล้เคียงกันทางความหมาย"),
                 ("Matching Score Normalization", "แปลงผลลัพธ์เป็นเปอร์เซ็นต์ (0 - 100%) หากมีวิชาตรงจะได้รับ Bonus Boost และเรียงลำดับติวเตอร์จากคะแนนสูงสุด")
             ], 
             accent_color=COLOR_INDIGO, icon="🎯")

    # ==========================================
    # SLIDE 8: TUTOR JOURNEY WORKFLOW
    # ==========================================
    s8 = prs.slides.add_slide(blank_layout)
    add_header(s8, "7. กระบวนการใช้งาน", "เส้นทางการใช้งานสำหรับผู้สอน / ติวเตอร์ (Tutor Journey Flow)", "ขั้นตอนที่สะดวก รวดเร็ว และปลอดภัยสำหรับติวเตอร์ในการลงทะเบียนและเปิดรับสอน")

    tutor_steps = [
        ("1. สมัครสมาชิกติวเตอร์", "กรอก Username และชื่อ-นามสกุลจริง เลือกประเภทบัญชีเป็น 'ติวเตอร์ (Tutor)'", COLOR_ACCENT, "📝"),
        ("2. สแกนบันทึกใบหน้า", "เปิดกล้องเว็บแคมผ่านระบบ จัดใบหน้าให้อยู่ในกรอบ แล้วกด 'บันทึกรูปเพื่อลงทะเบียนใบหน้า'", COLOR_PRIMARY, "📸"),
        ("3. สแกนใบหน้าเพื่อ Login", "กรอก Username และกดสแกนใบหน้า ระบบจะยืนยันตัวตนชีวมิติและเข้าสู่ระบบทันที", COLOR_INDIGO, "🔑"),
        ("4. บันทึกข้อมูลโปรไฟล์", "กรอกวิชาที่สอน, ความเชี่ยวชาญ, สไตล์การสอน, เรทราคา, พร้อม LINE ID และเบอร์โทรศัพท์", COLOR_SECONDARY, "📋"),
        ("5. รับคำขอนัดหมายและสอน", "รับแจ้งเตือนเมื่อมีนักเรียนจองเวลาเรียน ตรวจสอบตารางนัดหมาย และติดต่อผ่าน LINE", COLOR_LINE_GREEN, "🎓")
    ]

    t_w = Inches(2.15)
    t_gap = Inches(0.18)
    for i, (t_title, t_desc, t_color, t_icon) in enumerate(tutor_steps):
        tx = Inches(0.8) + i * (t_w + t_gap)
        c = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, tx, Inches(1.8), t_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Top Badge
        tbadge = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, tx, Inches(1.8), t_w, Inches(0.6))
        tbadge.fill.solid()
        tbadge.fill.fore_color.rgb = t_color
        tbadge.line.fill.background()
        btf = tbadge.text_frame
        btf.vertical_anchor = MSO_ANCHOR.MIDDLE
        bp = btf.paragraphs[0]
        bp.text = f"STEP 0{i+1}"
        bp.font.name = FONT_BOLD
        bp.font.size = Pt(12)
        bp.font.bold = True
        bp.font.color.rgb = COLOR_TEXT_LIGHT
        bp.alignment = PP_ALIGN.CENTER

        # Content
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.7)
        ctf.margin_left = ctf.margin_right = Inches(0.15)

        p1 = ctf.paragraphs[0]
        p1.text = f"{t_icon} {t_title}"
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_TEXT_MAIN
        p1.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = t_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 9: STUDENT JOURNEY WORKFLOW
    # ==========================================
    s9 = prs.slides.add_slide(blank_layout)
    add_header(s9, "8. กระบวนการใช้งาน", "เส้นทางการใช้งานสำหรับผู้เรียนและผู้ปกครอง (Student Journey Flow)", "ค้นหาติวเตอร์ที่ตรงสไตล์และงบประมาณได้อย่างแม่นยำ ผ่านกระบวนการที่เป็นมิตรกับผู้ใช้")

    student_steps = [
        ("1. ลงทะเบียนและสแกนใบหน้า", "สมัครสมาชิก เลือกบทบาท 'ผู้เรียน' และสแกนใบหน้าเพื่อความปลอดภัยและยืนยันตัวตน", COLOR_ACCENT, "👤"),
        ("2. ระบุความต้องการภาษาไทย", "พิมพ์ความต้องการอิสระ เช่น 'อยากได้พี่ใจดี สอนฟิสิกส์ ม.ปลาย เน้นปูพื้นฐานทำเกรด'", COLOR_INDIGO, "✍️"),
        ("3. กำหนดตัวกรอง (Filters)", "ระบุวิชาหลักที่เจาะจง (เช่น ฟิสิกส์) และกำหนดงบประมาณต่อชั่วโมงสูงสุด (เช่น 300 บาท)", COLOR_WARNING, "⚙️"),
        ("4. ดูผลลัพธ์ AI Matching", "ระบบแสดงคะแนนความเข้ากันได้ เช่น 'AI Match: 94.2%' พร้อมประวัติและสไตล์ของติวเตอร์", COLOR_PRIMARY, "📊"),
        ("5. ติดต่อนัดหมายเรียน", "กดทักแชทผ่าน LINE OA โดยตรง หรือส่งคำขอนัดหมายวันเวลาเรียนผ่านระบบอย่างเป็นทางการ", COLOR_LINE_GREEN, "🤝")
    ]

    for i, (s_title, s_desc, s_color, s_icon) in enumerate(student_steps):
        sx = Inches(0.8) + i * (t_w + t_gap)
        c = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, sx, Inches(1.8), t_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Top Badge
        sbadge = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, sx, Inches(1.8), t_w, Inches(0.6))
        sbadge.fill.solid()
        sbadge.fill.fore_color.rgb = s_color
        sbadge.line.fill.background()
        btf = sbadge.text_frame
        btf.vertical_anchor = MSO_ANCHOR.MIDDLE
        bp = btf.paragraphs[0]
        bp.text = f"STEP 0{i+1}"
        bp.font.name = FONT_BOLD
        bp.font.size = Pt(12)
        bp.font.bold = True
        bp.font.color.rgb = COLOR_TEXT_LIGHT
        bp.alignment = PP_ALIGN.CENTER

        # Content
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.7)
        ctf.margin_left = ctf.margin_right = Inches(0.15)

        p1 = ctf.paragraphs[0]
        p1.text = f"{s_icon} {s_title}"
        p1.font.name = FONT_BOLD
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_TEXT_MAIN
        p1.space_after = Pt(8)

        p2 = ctf.add_paragraph()
        p2.text = s_desc
        p2.font.name = FONT_MAIN
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 10: APPOINTMENT & DUAL COMMUNICATION
    # ==========================================
    s10 = prs.slides.add_slide(blank_layout)
    add_header(s10, "9. การสื่อสารและการนัดหมาย", "ระบบการนัดหมายและการติดต่อสื่อสาร 2 ช่องทาง (Dual Communication Channels)", "มอบความยืดหยุ่นสูงสุดด้วยการประสานระหว่าง LINE Chat และ In-App Scheduler")

    add_card(s10, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), 
             "ช่องทางที่ 1: Direct LINE OA Chat", 
             [
                 ("ทักแชททันทีด้วยคลิกเดียว", "มีปุ่ม 'ทักทาง LINE' เชื่อมต่อไปยัง LINE Official Account ของติวเตอร์ทันที"),
                 ("สอดคล้องกับพฤติกรรมคนไทย", "ผู้เรียนและผู้ปกครองคุ้นเคยกับการสอบถามรายละเอียด พูดคุยเบื้องต้นผ่าน LINE แชท"),
                 ("ส่งไฟล์เอกสารและภาพประกอบ", "ติวเตอร์สามารถส่งตัวอย่างเอกสารการสอน แผนการเรียน หรือรูปภาพผลงานการสอนผ่านแชทได้โดยตรง"),
                 ("การแจ้งเตือน Real-time", "ระบบสามารถส่ง Push Notification ผ่าน LINE เมื่อมีข้อความหรือคำขอใหม่")
             ], 
             accent_color=COLOR_LINE_GREEN, icon="💬")

    add_card(s10, Inches(6.9), Inches(1.8), Inches(5.6), Inches(4.8), 
             "ช่องทางที่ 2: In-App Appointment System", 
             [
                 ("เลือกวันและเวลาเรียนที่ต้องการ", "แบบฟอร์มให้ระบุวันที่ (YYYY-MM-DD) และช่วงเวลาเรียน (เช่น '13:00 - 15:00')"),
                 ("ระบุหัวข้อที่ต้องการเรียน (Notes)", "ผู้เรียนสามารถเขียนระบุเรื่องที่อยากเน้น เช่น 'ติวสอบกลางภาคบทการเคลื่อนที่'"),
                 ("ระบบจัดการสถานะคำขอ (Status Flow)", "คำขอจะเริ่มจาก 'Pending' เพื่อรอให้ติวเตอร์กดยืนยัน (Approved) หรือปฏิเสธ (Declined)"),
                 ("ตารางนัดหมายส่วนตัว (Dashboard)", "แสดงการ์ดรายการนัดหมายทั้งหมดของผู้เรียนและติวเตอร์ในหน้าจอแบบ Real-time")
             ], 
             accent_color=COLOR_PRIMARY, icon="📅")

    # ==========================================
    # SLIDE 11: DATA MODELS & SCHEMA DESIGN
    # ==========================================
    s11 = prs.slides.add_slide(blank_layout)
    add_header(s11, "10. การออกแบบฐานข้อมูล", "โมเดลข้อมูลและความสัมพันธ์ของฐานข้อมูล (Data Models & Schemas)", "โครงสร้างฐานข้อมูลเชิงสัมพันธ์ที่มีประสิทธิภาพ เชื่อมโยงข้อมูลผู้ใช้ ชีวมิติ และการนัดหมาย")

    models = [
        ("User (ตารางผู้ใช้หลัก)", [
            "id: Integer (Primary Key)",
            "username: String (Unique)",
            "name: String (ชื่อ-นามสกุลจริง)",
            "role: String ('student' / 'tutor')",
            "line_id: String (LINE User ID)",
            "registered_at: DateTime"
        ], COLOR_ACCENT, "👤"),
        ("BiometricProfile (ข้อมูลใบหน้า)", [
            "id: Integer (Primary Key)",
            "user_id: ForeignKey('users.id')",
            "face_landmarks: Text (Base64 JPEG)",
            "updated_at: DateTime",
            "One-to-One Cascade กับ User",
            "ใช้สำหรับสแกนเปรียบเทียบ LBPH"
        ], COLOR_PRIMARY, "🔒"),
        ("TutorProfile (ข้อมูลผู้สอน)", [
            "id: Integer (Primary Key)",
            "user_id: ForeignKey('users.id')",
            "education, subjects, expertise: Text",
            "teaching_style, availability: String",
            "price_per_hour: Integer",
            "contact_line, contact_phone, email: String",
            "appointment_instruction: Text"
        ], COLOR_INDIGO, "👨‍🏫"),
        ("Appointment (ตารางการนัดหมาย)", [
            "id: Integer (Primary Key)",
            "student_id: ForeignKey('users.id')",
            "tutor_id: ForeignKey('users.id')",
            "appointment_date: String (YYYY-MM-DD)",
            "appointment_time: String",
            "notes: Text (หัวข้อการเรียน)",
            "status: 'pending'/'approved'/'declined'"
        ], COLOR_SECONDARY, "📅")
    ]

    m_w = Inches(2.7)
    m_gap = Inches(0.25)
    for i, (m_title, m_fields, m_color, m_icon) in enumerate(models):
        mx = Inches(0.8) + i * (m_w + m_gap)
        c = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, mx, Inches(1.8), m_w, Inches(4.8))
        c.fill.solid()
        c.fill.fore_color.rgb = COLOR_CARD_BG
        c.line.color.rgb = COLOR_CARD_BORDER
        c.line.width = Pt(1)

        # Top Strip
        mstrip = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, mx, Inches(1.8), m_w, Inches(0.6))
        mstrip.fill.solid()
        mstrip.fill.fore_color.rgb = m_color
        mstrip.line.fill.background()
        mtf = mstrip.text_frame
        mtf.vertical_anchor = MSO_ANCHOR.MIDDLE
        mp = mtf.paragraphs[0]
        mp.text = f"{m_icon} {m_title}"
        mp.font.name = FONT_BOLD
        mp.font.size = Pt(11)
        mp.font.bold = True
        mp.font.color.rgb = COLOR_TEXT_LIGHT
        mp.alignment = PP_ALIGN.CENTER

        # Content
        ctf = c.text_frame
        ctf.word_wrap = True
        ctf.margin_top = Inches(0.75)
        ctf.margin_left = ctf.margin_right = Inches(0.15)

        for f_idx, field in enumerate(m_fields):
            p = ctf.paragraphs[0] if f_idx == 0 else ctf.add_paragraph()
            p.text = "• " + field
            p.font.name = FONT_MAIN
            p.font.size = Pt(10)
            p.font.color.rgb = COLOR_TEXT_MUTED
            p.space_after = Pt(4)

    # ==========================================
    # SLIDE 12: IMPLEMENTATION ROADMAP
    # ==========================================
    s12 = prs.slides.add_slide(blank_layout)
    add_header(s12, "11. แผนการพัฒนาระบบ", "แผนงานและขั้นตอนการพัฒนาระบบ 4 ระยะ (Development Roadmap)", "กระบวนการพัฒนาอย่างเป็นขั้นตอน จากโครงสร้างพื้นฐานสู่ระบบที่พร้อมใช้งานจริง")

    phases = [
        ("Phase 01", "Project Foundation & Environment Setup", 
         "ติดตั้ง Environment, กำหนดค่า FastAPI, SQLite/PostgreSQL Database, ติดตั้ง PyThaiNLP, OpenCV และเชื่อมต่อกับ LINE Developer Console สร้าง Webhook API และ LIFF App ID", 
         COLOR_ACCENT, "⚙️"),
        ("Phase 02", "Biometric Security & Camera Integration", 
         "พัฒนาหน้าจอเว็บแอป LIFF เข้าถึงกล้อง WebRTC บนอุปกรณ์มือถือและเบราว์เซอร์ พัฒนาโมเดล Haar Cascade และ LBPH สำหรับการบันทึกภาพและเปรียบเทียบใบหน้ายืนยันตัวตน", 
         COLOR_PRIMARY, "🔒"),
        ("Phase 03", "Tutor Profile System & NLP Pipeline", 
         "สร้างแบบฟอร์มลงทะเบียนประวัติ ความถนัด และสไตล์การสอนของติวเตอร์ พัฒนา Pipeline การตัดคำภาษาไทยด้วย PyThaiNLP และคำนวณเวกเตอร์ความเชี่ยวชาญเก็บลงฐานข้อมูล", 
         COLOR_INDIGO, "🧠"),
        ("Phase 04", "AI Matching Engine & Dual Communication", 
         "พัฒนาระบบคำนวณ TF-IDF & Cosine Similarity, ตัวกรองราคาและวิชา, การส่ง Flex Message และระบบจองนัดหมายเวลาเรียน พร้อมทดสอบ End-to-End Test ครบวงจร", 
         COLOR_SECONDARY, "🚀")
    ]

    p_h = Inches(1.1)
    p_gap = Inches(0.15)
    p_start_y = Inches(1.8)
    for i, (p_num, p_title, p_desc, p_color, p_icon) in enumerate(phases):
        py = p_start_y + i * (p_h + p_gap)
        
        box = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), py, Inches(11.7), p_h)
        box.fill.solid()
        box.fill.fore_color.rgb = COLOR_CARD_BG
        box.line.color.rgb = COLOR_CARD_BORDER
        box.line.width = Pt(1)

        # Left Step Indicator
        step_box = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), py, Inches(1.8), p_h)
        step_box.fill.solid()
        step_box.fill.fore_color.rgb = p_color
        step_box.line.fill.background()
        
        stf = step_box.text_frame
        stf.vertical_anchor = MSO_ANCHOR.MIDDLE
        sp = stf.paragraphs[0]
        sp.text = f"{p_icon}\n{p_num}"
        sp.font.name = FONT_BOLD
        sp.font.size = Pt(13)
        sp.font.bold = True
        sp.font.color.rgb = COLOR_TEXT_LIGHT
        sp.alignment = PP_ALIGN.CENTER

        # Content Text
        ct = s12.shapes.add_textbox(Inches(2.8), py + Inches(0.1), Inches(9.5), p_h - Inches(0.2))
        ctf = ct.text_frame
        ctf.word_wrap = True
        ctf.margin_top = ctf.margin_bottom = 0
        
        cp1 = ctf.paragraphs[0]
        cp1.text = p_title
        cp1.font.name = FONT_BOLD
        cp1.font.size = Pt(13)
        cp1.font.bold = True
        cp1.font.color.rgb = p_color

        cp2 = ctf.add_paragraph()
        cp2.text = p_desc
        cp2.font.name = FONT_MAIN
        cp2.font.size = Pt(10.5)
        cp2.font.color.rgb = COLOR_TEXT_MUTED

    # ==========================================
    # SLIDE 13: BUSINESS VALUE & COMPARISON
    # ==========================================
    s13 = prs.slides.add_slide(blank_layout)
    add_header(s13, "12. คุณค่าและประโยชน์ของระบบ", "ประโยชน์ที่ได้รับและการเปรียบเทียบ (Business Value & Impact)", "ยกระดับประสบการณ์การเรียนการสอนพิเศษนอกห้องเรียนอย่างเห็นได้ชัด")

    add_card(s13, Inches(0.8), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ประโยชน์สำหรับผู้เรียน & ผู้ปกครอง", 
             [
                 ("ได้ติวเตอร์ที่ตรงสไตล์จริง", "ค้นพบผู้สอนที่มีสไตล์ตรงกับตนเอง เช่น สอนช้าๆ ปูพื้นฐาน หรือเน้นลุยโจทย์"),
                 ("ประหยัดค่าใช้จ่ายและเวลา", "ระบบกรองงบประมาณตั้งแต่แรก และลดการเสียเงินทดลองเรียนกับติวเตอร์ที่ไม่ใช่"),
                 ("ความปลอดภัยและความมั่นใจ", "ติวเตอร์ทุกคนผ่านการยืนยันตัวตนด้วยใบหน้าชีวมิติ ปลอดภัยสูงสุด"),
                 ("ใช้งานง่ายบนมือถือ", "ไม่ต้องโหลดแอปเพิ่ม เข้าถึงผ่าน LINE ที่ใช้งานอยู่แล้วในชีวิตประจำวัน")
             ], 
             accent_color=COLOR_ACCENT, icon="🎓")

    add_card(s13, Inches(4.85), Inches(1.8), Inches(3.6), Inches(4.8), 
             "ประโยชน์สำหรับติวเตอร์ / ผู้สอน", 
             [
                 ("เจอนักเรียนที่ตรงกับความถนัด", "AI จะแนะนำติวเตอร์ให้นักเรียนที่ต้องการสไตล์การสอนนั้นๆ โดยเฉพาะ"),
                 ("สร้างความน่าเชื่อถือระดับสูง", "การมีโปรไฟล์ที่ผ่านการตรวจสอบใบหน้า ช่วยเพิ่มความไว้วางใจจากผู้ปกครอง"),
                 ("จัดการตารางเรียนมีประสิทธิภาพ", "มีระบบรับคำขอนัดหมาย ตรวจสอบสถานะ และนัดวันเวลาเรียนอย่างเป็นระบบ"),
                 ("ช่องทางติดต่อตรงผ่าน LINE", "สามารถคุยรายละเอียดคอร์สเรียนกับนักเรียนได้สะดวกรวดเร็ว")
             ], 
             accent_color=COLOR_PRIMARY, icon="👨‍🏫")

    add_card(s13, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), 
             "เปรียบเทียบ Before vs After", 
             [
                 ("การค้นหาติวเตอร์", "เดิม: เลื่อนหาตามป้ายประกาศหรือโพสต์โซเชียล\nใหม่: AI แนะนำติวเตอร์ที่เหมาะสมที่สุดทันที"),
                 ("การตรวจสอบตัวตน", "เดิม: ไม่มีระบบยืนยัน เสี่ยงถูกสวมรอย\nใหม่: บังคับ Face ID ป้องกันการแอบอ้าง 100%"),
                 ("การประสานงาน", "เดิม: ติดต่อผ่านเบอร์โทรศัพท์ ตกหล่นง่าย\nใหม่: นัดหมายในระบบ และทักแชทผ่าน LINE OA")
             ], 
             accent_color=COLOR_SECONDARY, icon="📈")

    # ==========================================
    # SLIDE 14: FUTURE ROADMAP & SCALABILITY
    # ==========================================
    s14 = prs.slides.add_slide(blank_layout)
    add_header(s14, "13. การต่อยอดในอนาคต", "ทิศทางการพัฒนาต่อยอดสู่ระดับ Enterprise (Future Roadmap)", "แนวทางการขยายขีดความสามารถของระบบด้วยเทคโนโลยี AI ระดับแนวหน้า")

    roadmaps = [
        ("Generative AI & LLMs", "บูรณาการ Gemini API", "นำ Large Language Models มาใช้วิเคราะห์ความต้องการแบบ Multi-turn Chatbot สนทนาถาม-ตอบ แนะนำติวเตอร์เสมือนมีที่ปรึกษาส่วนตัว", COLOR_PRIMARY, "🤖"),
        ("Enterprise Vector Search", "PostgreSQL + pgvector", "ยกระดับสู่ Vector Database เช่น pgvector หรือ Qdrant เพื่อรองรับการค้นหา Semantic Match จากโปรไฟล์ติวเตอร์นับแสนรายในระดับเสี้ยววินาที", COLOR_ACCENT, "⚡"),
        ("Integrated Payment Gateway", "PromptPay QR & LINE Pay", "ระบบชำระค่าเรียนออนไลน์อัตโนมัติ พร้อมระบบ Escrow (พักเงินไว้จนกว่าจะเริ่มคลาสเรียน) เพื่อความปลอดภัยของทั้งสองฝ่าย", COLOR_LINE_GREEN, "💳"),
        ("Learning Analytics Loop", "ระบบติดตามและวิเคราะห์ผล", "ระบบบันทึกพัฒนาการของผู้เรียนหลังจบคลาส และนำคะแนนรีวิวความพึงพอใจมาปรับค่าน้ำหนักของ Matching Algorithm โดยอัตโนมัติ", COLOR_WARNING, "📊")
    ]

    r_w = Inches(5.6)
    r_h = Inches(2.25)
    for i, (r_title, r_sub, r_desc, r_color, r_icon) in enumerate(roadmaps):
        col = i % 2
        row = i // 2
        rx = Inches(0.8) + col * Inches(6.1)
        ry = Inches(1.8) + row * Inches(2.55)

        card = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, rx, ry, r_w, r_h)
        card.fill.solid()
        card.fill.fore_color.rgb = COLOR_CARD_BG
        card.line.color.rgb = COLOR_CARD_BORDER
        card.line.width = Pt(1)

        # Left Accent Stripe
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
        p1.font.size = Pt(14)
        p1.font.bold = True
        p1.font.color.rgb = r_color
        p1.space_after = Pt(2)

        p2 = ctf.add_paragraph()
        p2.text = r_sub
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
    # SLIDE 15: CONCLUSION & DEMONSTRATION
    # ==========================================
    s15 = prs.slides.add_slide(blank_layout)
    bg15 = s15.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg15.fill.solid()
    bg15.fill.fore_color.rgb = COLOR_BG_DARK
    bg15.line.fill.background()

    # Glowing Accent Shapes
    acc15 = s15.shapes.add_shape(MSO_SHAPE.OVAL, Inches(8.5), Inches(1.5), Inches(6.0), Inches(6.0))
    acc15.fill.solid()
    acc15.fill.fore_color.rgb = RGBColor(30, 27, 75)
    acc15.line.fill.background()

    tbox15 = s15.shapes.add_textbox(Inches(1.2), Inches(1.5), Inches(11.0), Inches(4.5))
    tf15 = tbox15.text_frame
    tf15.word_wrap = True

    p_b15 = tf15.paragraphs[0]
    p_b15.text = "EXECUTIVE SUMMARY & SYSTEM DEMONSTRATION"
    p_b15.font.name = FONT_BOLD
    p_b15.font.size = Pt(12)
    p_b15.font.bold = True
    p_b15.font.color.rgb = RGBColor(236, 72, 153)
    p_b15.space_after = Pt(14)

    p_m15 = tf15.add_paragraph()
    p_m15.text = "บทสรุปโครงการ TutorMatch AI"
    p_m15.font.name = FONT_BOLD
    p_m15.font.size = Pt(36)
    p_m15.font.bold = True
    p_m15.font.color.rgb = COLOR_TEXT_LIGHT
    p_m15.space_after = Pt(12)

    p_d15 = tf15.add_paragraph()
    p_d15.text = "TutorMatch AI คือการผสานอย่างลงตัวระหว่าง 'ความสะดวกในการเข้าถึง (LINE Ecosystem)' + 'ความปลอดภัยขั้นสูงสุด (Face Biometrics)' + 'ความชาญฉลาดในการวิเคราะห์ (Thai NLP Semantic Matching)'\n\nระบบได้รับการออกแบบและพัฒนาให้สามารถใช้งานได้จริง ครอบคลุมทั้งฝั่งผู้เรียนและผู้สอน พร้อมขับเคลื่อนการศึกษาพิเศษนอกห้องเรียนของไทยให้ก้าวสู่ยุค AI อย่างมั่นคง"
    p_d15.font.name = FONT_MAIN
    p_d15.font.size = Pt(14)
    p_d15.font.color.rgb = RGBColor(203, 213, 225)
    p_d15.space_after = Pt(24)

    p_q = tf15.add_paragraph()
    p_q.text = "🎉 ขอขอบพระคุณทุกท่าน  |  เข้าสู่ช่วงถาม-ตอบ (Q&A Session)"
    p_q.font.name = FONT_BOLD
    p_q.font.size = Pt(18)
    p_q.font.bold = True
    p_q.font.color.rgb = RGBColor(167, 139, 250)

    # Save presentation
    output_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "TutorMatch_AI_Concept_and_Architecture.pptx")
    prs.save(output_path)
    print(f"Successfully generated PowerPoint presentation at: {output_path}")

if __name__ == "__main__":
    create_deck()
