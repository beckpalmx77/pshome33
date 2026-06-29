import os
import re
import requests
from reportlab.lib.pagesizes import A4
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas
from pythainlp.tokenize import word_tokenize

# --- Configuration and Download Fonts ---
FONT_REGULAR_URL = "https://github.com/google/fonts/raw/main/ofl/sarabun/Sarabun-Regular.ttf"
FONT_BOLD_URL = "https://github.com/google/fonts/raw/main/ofl/sarabun/Sarabun-Bold.ttf"
FONT_REGULAR_PATH = "Sarabun-Regular.ttf"
FONT_BOLD_PATH = "Sarabun-Bold.ttf"

def download_file(url, filename):
    if not os.path.exists(filename):
        print(f"Downloading {filename}...")
        response = requests.get(url)
        with open(filename, 'wb') as f:
            f.write(response.content)
        print("Done.")

download_file(FONT_REGULAR_URL, FONT_REGULAR_PATH)
download_file(FONT_BOLD_URL, FONT_BOLD_PATH)

# Register Fonts
pdfmetrics.registerFont(TTFont('Sarabun', FONT_REGULAR_PATH))
pdfmetrics.registerFont(TTFont('Sarabun-Bold', FONT_BOLD_PATH))

# --- Helper for Thai Word Wrapping in ReportLab ---
def thai_text(text):
    if not text:
        return ""
    # Split by HTML tags to prevent tokenizing inside HTML tags (e.g. <b>, <i>, <font>)
    parts = re.split(r'(<[^>]+>)', text)
    result = []
    for part in parts:
        if part.startswith('<') and part.endswith('>'):
            result.append(part)
        else:
            # Tokenize and join with zero-width space (\u200b)
            words = word_tokenize(part, engine='newmm')
            result.append('\u200b'.join(words))
    return "".join(result)

# --- NumberedCanvas to support "Page X of Y" ---
class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_number(num_pages)
            super().showPage()
        super().save()

    def draw_page_number(self, page_count):
        self.saveState()
        self.setFont("Sarabun", 9)
        self.setFillColor(HexColor("#64748b"))
        
        # Draw Header (except page 1)
        if self._pageNumber > 1:
            self.drawString(1.5 * cm, 28 * cm, "TutorMatch AI - Concept & Architecture Design")
            self.setStrokeColor(HexColor("#cbd5e1"))
            self.setLineWidth(0.5)
            self.line(1.5 * cm, 27.7 * cm, 19.5 * cm, 27.7 * cm)
            
        # Draw Footer
        page_text = f"หน้า {self._pageNumber} จาก {page_count}"
        self.drawRightString(19.5 * cm, 1 * cm, page_text)
        self.drawString(1.5 * cm, 1 * cm, "เอกสารแนวคิดและสถาปัตยกรรมระบบจับคู่ติวเตอร์ด้วย AI")
        self.restoreState()

# --- Main PDF Generator ---
def generate_pdf():
    pdf_filename = "readme_concept.pdf"
    fallback_filename = "readme_concept_updated.pdf"
    
    styles = getSampleStyleSheet()
    
    # Custom Paragraph Styles
    style_title = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Sarabun-Bold',
        fontSize=24,
        leading=30,
        textColor=HexColor("#4f46e5"), # Indigo 600
        alignment=1, # Center
        spaceAfter=10
    )
    
    style_subtitle = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=12,
        leading=16,
        textColor=HexColor("#475569"), # Slate 600
        alignment=1,
        spaceAfter=30
    )
    
    style_h1 = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Heading1'],
        fontName='Sarabun-Bold',
        fontSize=16,
        leading=22,
        textColor=HexColor("#7c3aed"), # Violet 600
        spaceBefore=15,
        spaceAfter=10,
        keepWithNext=True
    )

    style_h2 = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Heading2'],
        fontName='Sarabun-Bold',
        fontSize=12,
        leading=18,
        textColor=HexColor("#0f172a"), # Slate 900
        spaceBefore=10,
        spaceAfter=6,
        keepWithNext=True
    )
    
    style_body = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=10,
        leading=15,
        textColor=HexColor("#334155"), # Slate 700
        spaceAfter=8
    )

    style_bullet = ParagraphStyle(
        'Bullet_Custom',
        parent=style_body,
        leftIndent=15,
        firstLineIndent=-10,
        spaceAfter=4
    )

    style_code = ParagraphStyle(
        'Code_Custom',
        parent=styles['Code'],
        fontName='Sarabun', # Use Sarabun for comments in code
        fontSize=8.5,
        leading=12,
        textColor=HexColor("#0f172a"),
        backColor=HexColor("#f1f5f9"),
        borderColor=HexColor("#e2e8f0"),
        borderWidth=0.5,
        borderPadding=10,
        spaceBefore=10,
        spaceAfter=10
    )

    # Styles for table cells to parse Paragraph correctly
    style_table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Sarabun-Bold',
        fontSize=10,
        leading=14,
        textColor=HexColor("#0f172a")
    )
    style_table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=9.5,
        leading=13.5,
        textColor=HexColor("#334155")
    )

    story = []
    
    # --- Title Page ---
    story.append(Spacer(1, 2 * cm))
    story.append(Paragraph(thai_text("TutorMatch AI"), style_title))
    story.append(Paragraph(thai_text("ระบบจับคู่ติวเตอร์อัจฉริยะด้วยปัญญาประดิษฐ์ ผ่าน LINE Official Account"), style_subtitle))
    story.append(Spacer(1, 1 * cm))
    
    # Metadata Box (Table) wrapped in Paragraphs
    metadata_data = [
        [Paragraph(thai_text("<b>ประเภทระบบ:</b>"), style_body), Paragraph(thai_text("Web Application (LINE OA & LIFF Integration)"), style_body)],
        [Paragraph(thai_text("<b>เทคโนโลยีหลัก:</b>"), style_body), Paragraph(thai_text("Python, FastAPI, DeepFace, Sentence-Transformers, PostgreSQL"), style_body)],
        [Paragraph(thai_text("<b>ผู้พัฒนา:</b>"), style_body), Paragraph(thai_text("Antigravity AI Assistant"), style_body)],
        [Paragraph(thai_text("<b>เอกสาร:</b>"), style_body), Paragraph(thai_text("แนวคิดระบบ สถาปัตยกรรม และขั้นตอนการดำเนินงาน (Concept & Architecture Design)"), style_body)]
    ]
    t_meta = Table(metadata_data, colWidths=[3.5 * cm, 13 * cm])
    t_meta.setStyle(TableStyle([
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('LINEBELOW', (0,0), (-1,-1), 0.5, HexColor("#f1f5f9")),
    ]))
    story.append(t_meta)
    story.append(Spacer(1, 2 * cm))
    
    # Executive Summary Card
    summary_text = (
        "<b>บทสรุปผู้บริหาร (Executive Summary):</b><br/>"
        "TutorMatch AI คือระบบจับคู่ติวเตอร์อัจฉริยะที่อำนวยความสะดวกให้ผู้เรียนสามารถค้นหาติวเตอร์ที่เหมาะสมที่สุดได้ผ่าน LINE "
        "โดยมีจุดเด่นในเรื่องความปลอดภัยจากการยืนยันตัวตนด้วยใบหน้า (Face Verification) ผ่าน LINE LIFF "
        "และกลไกการจับคู่ด้วย AI ประมวลผลภาษาธรรมชาติเชิงความหมาย (Semantic Matching) "
        "ช่วยให้จับคู่ติวเตอร์ได้ตรงตามทักษะ ความรู้ ความถนัด และรูปแบบการเรียนรู้ที่นักเรียนต้องการอย่างแท้จริง"
    )
    t_sum = Table([[Paragraph(thai_text(summary_text), style_body)]], colWidths=[17.5 * cm])
    t_sum.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), HexColor("#f5f3ff")), # Light Purple
        ('BOX', (0,0), (-1,-1), 1, HexColor("#ddd6fe")),
        ('PADDING', (0,0), (-1,-1), 15),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_sum)
    story.append(PageBreak())
    
    # --- Section 1: แนวคิดของระบบ ---
    story.append(Paragraph(thai_text("1. แนวคิดและที่มาของโครงการ (System Concept)"), style_h1))
    story.append(Paragraph(thai_text(
        "ในระบบการเรียนการสอนพิเศษในปัจจุบัน ปัญหาที่มักพบเจอคือความไม่เหมาะสมกันระหว่างสไตล์การสอนของติวเตอร์และพฤติกรรมการเรียนของนักเรียน "
        "รวมถึงความปลอดภัยในการดำเนินกิจกรรม ซึ่งระบบ TutorMatch AI ถูกสร้างขึ้นเพื่อแก้ไขปัญหานี้ด้วยองค์ประกอบ 2 ด้านหลัก:"
    ), style_body))
    
    story.append(Paragraph(thai_text("<b>• ระบบการยืนยันตัวตนด้วยใบหน้า (Face Verification)</b>"), style_h2))
    story.append(Paragraph(thai_text(
        "ก่อนการใช้งานหรือลงทะเบียนเพื่อความปลอดภัย ทั้งติวเตอร์และนักเรียนจะต้องทำการลงทะเบียนใบหน้าผ่านกล้องหน้าของ LINE LIFF "
        "จากนั้นทุกครั้งที่มีการเข้าใช้งานฟังก์ชันสำคัญ ระบบจะทำการสแกนใบหน้าและแปลงค่าเป็น Face Embedding เพื่อนำมาเปรียบเทียบในฐานข้อมูล "
        "เป็นการยืนยันความปลอดภัยและตัวตนจริงของผู้ใช้งานในระบบ"
    ), style_body))
    
    story.append(Paragraph(thai_text("<b>• ระบบการจับคู่เชิงความหมาย (Semantic Matching Engine)</b>"), style_h2))
    story.append(Paragraph(thai_text(
        "แตกต่างจากระบบแบบเก่าที่ค้นหาจากคำสำคัญ (Keywords) เท่านั้น เนื่องจากผู้เรียนบางคนอาจเขียนระบุรายละเอียดเป็นภาษาธรรมชาติที่เป็นความต้องการจริงๆ "
        "เช่น 'หาพี่สอนเลขใจดี เน้นปูพื้นฐานตั้งแต่ม.ต้น ไม่ดุเป็นกันเอง' ระบบจะใช้ AI ในการถอดความหมายของความต้องการดังกล่าว "
        "แล้วจับคู่กับประวัติ ทักษะ ความถนัด และจุดเด่นของติวเตอร์ที่บันทึกไว้ในระบบ เพื่อคำนวณเป็น Matching Score ที่ดีที่สุด"
    ), style_body))
    
    story.append(Spacer(1, 0.5 * cm))
    
    # --- Section 2: สถาปัตยกรรมระบบ ---
    story.append(Paragraph(thai_text("2. สถาปัตยกรรมและการไหลของข้อมูล (System Architecture)"), style_h1))
    story.append(Paragraph(thai_text(
        "สถาปัตยกรรมระบบถูกออกแบบเป็น Micro-component ที่เชื่อมโยงเข้ากับระบบนิเวศของ LINE ดังนี้:"
    ), style_body))
    
    # Table cells wrapped in Paragraphs
    arch_data = [
        [
            Paragraph(thai_text("<b>ส่วนประกอบ (Component)</b>"), style_table_header), 
            Paragraph(thai_text("<b>หน้าที่การทำงาน (Responsibility)</b>"), style_table_header)
        ],
        [
            Paragraph(thai_text("LINE OA & Webhook"), style_table_cell), 
            Paragraph(thai_text("เป็นช่องทางรับส่งข้อความหลัก ส่งข้อมูลกิจกรรมจากห้องแชท LINE ไปยังเซิร์ฟเวอร์หลังบ้าน"), style_table_cell)
        ],
        [
            Paragraph(thai_text("LINE LIFF (Frontend)"), style_table_cell), 
            Paragraph(thai_text("หน้าจอเว็บฝังใน LINE สำหรับหน้ากรอกฟอร์ม สมัครสมาชิก และเรียกใช้กล้องหน้าสำหรับสแกนใบหน้า"), style_table_cell)
        ],
        [
            Paragraph(thai_text("FastAPI Backend"), style_table_cell), 
            Paragraph(thai_text("ระบบประมวลผลหลัก (API Gateway) จัดการสิทธิ์การใช้งาน บันทึกข้อมูล และประสานงานกับโมดูล AI"), style_table_cell)
        ],
        [
            Paragraph(thai_text("AI Engines"), style_table_cell), 
            Paragraph(thai_text("1) Face Verification (ประมวลรูปภาพด้วย DeepFace)<br/>2) Matching Engine (จับคู่ข้อความด้วย Sentence-Transformers)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("Database (PostgreSQL)"), style_table_cell), 
            Paragraph(thai_text("จัดเก็บข้อมูลติวเตอร์ ข้อมูลนักเรียน รวมถึงเวกเตอร์ของใบหน้าและประวัติติวเตอร์ (pgvector)"), style_table_cell)
        ],
    ]
    t_arch = Table(arch_data, colWidths=[5.5 * cm, 12.0 * cm])
    t_arch.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#e2e8f0")),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#cbd5e1")),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_arch)
    
    story.append(PageBreak())
    
    # --- Section 3: ชุดเทคโนโลยี ---
    story.append(Paragraph(thai_text("3. เทคโนโลยีที่เลือกใช้ (Technology Stack)"), style_h1))
    
    tech_categories = [
        ("Backend Web Framework", "Python 3.10+, FastAPI, Uvicorn, Requests (ยืดหยุ่น รวดเร็ว รองรับงาน Async)"),
        ("LINE Integration", "LINE Messaging API SDK, LINE LIFF (HTML5/CSS/JavaScript สำหรับหน้าจอเว็บย่อย)"),
        ("Face Recognition (AI)", "DeepFace, OpenCV, MediaPipe (สกัดลักษณะใบหน้าและวัดระยะห่างความเหมือน)"),
        ("Natural Language Processing", "Sentence-Transformers, PyThaiNLP, Gemini API (สำหรับประมวลภาษาไทยและทำ Semantic Matching)"),
        ("Database System", "PostgreSQL พร้อม Extension pgvector หรือ SQLite (สำหรับช่วงเริ่มต้นพัฒนา)"),
    ]
    
    for category, detail in tech_categories:
        story.append(Paragraph(thai_text(f"• <b>{category}:</b> {detail}"), style_bullet))
        
    story.append(Spacer(1, 0.5 * cm))
    
    # --- Section 4: AI Logic ---
    story.append(Paragraph(thai_text("4. กลไก AI และอัลกอริทึม (AI Engine Logic)"), style_h1))
    
    story.append(Paragraph(thai_text("<b>• ขั้นตอนการยืนยันตัวตนด้วยใบหน้า:</b>"), style_h2))
    story.append(Paragraph(thai_text(
        "1. ผู้ใช้ถ่ายภาพใบหน้าสดจากกล้องบน LINE LIFF ส่งไปยัง FastAPI Backend<br/>"
        "2. API ส่งภาพให้โมดูล <b>DeepFace</b> สกัดเวกเตอร์ลักษณะใบหน้าออกมา (128 หรือ 512 มิติ)<br/>"
        "3. เปรียบเทียบเวกเตอร์ใหม่กับเวกเตอร์เดิมในฐานข้อมูลด้วยระยะห่าง Cosine Distance<br/>"
        "4. หากระยะห่างน้อยกว่าเกณฑ์ที่กำหนด (เช่น &lt; 0.40) ถือว่าผ่านการตรวจสอบยืนยันตัวตน"
    ), style_bullet))
    
    code_example = (
        "# code snippet: การเปรียบเทียบใบหน้าในหลังบ้าน\n"
        "from deepface import DeepFace\n\n"
        "def verify_user_face(registered_img, current_img):\n"
        "    # เรียกใช้ DeepFace เปรียบเทียบเวกเตอร์สองภาพ\n"
        "    result = DeepFace.verify(\n"
        "        img1_path=registered_img, \n"
        "        img2_path=current_img, \n"
        "        model_name='ArcFace'\n"
        "    )\n"
        "    return result['verified'], result['distance']"
    )
    story.append(Paragraph(thai_text(code_example.replace("\n", "<br/>").replace(" ", "&nbsp;")), style_code))

    story.append(Paragraph(thai_text("<b>• ขั้นตอนการจับคู่ติวเตอร์เชิงความหมาย:</b>"), style_h2))
    story.append(Paragraph(thai_text(
        "1. <b>สร้างเวกเตอร์โปรไฟล์:</b> ระบบแปลงข้อมูลของติวเตอร์เป็น Vector Embedding เก็บใน PostgreSQL (pgvector)<br/>"
        "2. <b>ประมวลเวกเตอร์ความต้องการ:</b> นักเรียนกรอกข้อมูลความต้องการและถูกแปลงเป็นเวกเตอร์ด้วย Sentence-Transformer ตัวเดียวกัน<br/>"
        "3. <b>เปรียบเทียบคะแนนความเหมือน:</b> ค้นหาและดึงประวัติติวเตอร์ที่มีค่าความใกล้เคียงสูงสุด 3 อันดับแรก ด้วยคำสั่ง Cosine Similarity<br/>"
        "4. <b>การคัดกรองเบื้องต้น (Hard Filtering):</b> กรองเฉพาะติวเตอร์ที่วิชาสอนตรงและมีเวลาว่างตรงกับงบประมาณของนักเรียนก่อนคำนวณ"
    ), style_bullet))
    
    story.append(PageBreak())
    
    # --- Section 5: Implementation Steps ---
    story.append(Paragraph(thai_text("5. ขั้นตอนการพัฒนาระบบทีละขั้น (Implementation Plan)"), style_h1))
    
    steps = [
        ("ขั้นตอนที่ 1", "การตั้งค่าโครงการและการจัดเตรียมสิ่งแวดล้อม", 
         "ติดตั้งโปรแกรมและไลบรารีที่จำเป็น (FastAPI, PyThaiNLP, DeepFace) พร้อมเตรียมบัญชีผู้ใช้ LINE Developers เพื่อรับ Token และเตรียมสร้าง Channel Webhook ในส่วนควบคุม"),
        ("ขั้นตอนที่ 2", "พัฒนาระบบยืนยันตัวตนด้วยใบหน้า", 
         "เขียนกล้องหน้าบน LINE LIFF โดยให้เว็บของ LIFF สามารถจับภาพใบหน้าส่งเป็น Base64 หรืออัปโหลดไฟล์ และเขียน API ฝั่ง Python เพื่อทำการเปรียบเทียบรูปภาพกับฐานข้อมูลในลักษณะของ Metric Learning"),
        ("ขั้นตอนที่ 3", "พัฒนาระบบจัดการประวัติติวเตอร์และเก็บข้อมูลความเชี่ยวชาญ", 
         "สร้างหน้าเว็บฟอร์มให้ติวเตอร์กรอกข้อมูลความถนัด และประวัติต่างๆ จากนั้นฝั่งหลังบ้านจะดึงข้อมูลนี้มาผ่านกระบวนการ Embeddings เพื่อเปลี่ยนเป็นข้อมูลเชิงตัวเลขแล้วบันทึกลงสู่ฐานข้อมูล"),
        ("ขั้นตอนที่ 4", "สร้างหน้าจอสำหรับนักเรียน และเขียน AI Matching Engine", 
         "พัฒนาหน้าจอการป้อนความต้องการของนักเรียน และโมดูล AI ที่ทำการค้นหาเวกเตอร์ที่ใกล้เคียงที่สุด จากนั้นระบบจะแสดงผลลัพธ์ติวเตอร์ที่แนะนำเป็น Flex Message ผ่านทางแชท LINE OA เพื่อให้ติดต่อพูดคุยตกลงเรียนกันต่อไป")
    ]
    
    for idx, name, desc in steps:
        story.append(Paragraph(thai_text(f"<b>{idx}: {name}</b>"), style_h2))
        story.append(Paragraph(thai_text(desc), style_body))
        story.append(Spacer(1, 0.2 * cm))
        
    # Build with fallback in case of locks
    try:
        doc = SimpleDocTemplate(
            pdf_filename,
            pagesize=A4,
            leftMargin=1.5 * cm,
            rightMargin=1.5 * cm,
            topMargin=2.0 * cm,
            bottomMargin=2.0 * cm
        )
        doc.build(story, canvasmaker=NumberedCanvas)
        print("Concept PDF generation completed.")
    except PermissionError:
        print(f"Permission denied on {pdf_filename}. Retrying with fallback: {fallback_filename}")
        doc = SimpleDocTemplate(
            fallback_filename,
            pagesize=A4,
            leftMargin=1.5 * cm,
            rightMargin=1.5 * cm,
            topMargin=2.0 * cm,
            bottomMargin=2.0 * cm
        )
        doc.build(story, canvasmaker=NumberedCanvas)
        print(f"Concept PDF generation completed (saved as {fallback_filename}).")

if __name__ == "__main__":
    generate_pdf()
