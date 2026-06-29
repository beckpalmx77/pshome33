import os
import re
import html
from reportlab.lib.pagesizes import A4
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas
from pythainlp.tokenize import word_tokenize

# --- Register Fonts (Use local Sarabun TTF files) ---
FONT_REGULAR_PATH = "Sarabun-Regular.ttf"
FONT_BOLD_PATH = "Sarabun-Bold.ttf"

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

# --- Syntax Highlighting Helpers ---
def highlight_python_line(line):
    # Escape HTML first
    line = html.escape(line)
    
    # Preserve double spaces as non-breaking spaces
    line = line.replace('  ', '&nbsp;&nbsp;')
    
    # Extract comments to avoid highlighting within them
    comment = ""
    if '#' in line:
        parts = line.split('#', 1)
        line = parts[0]
        comment = f'<font color="#64748b">#{parts[1]}</font>'
        
    # Highlight strings
    line = re.sub(r'(&quot;.*?&quot;|\&#x27;.*?\&#x27;)', r'<font color="#059669">\1</font>', line)
    
    # Highlight keywords
    keywords = [
        'def', 'class', 'import', 'from', 'return', 'if', 'elif', 'else', 
        'try', 'except', 'finally', 'for', 'while', 'in', 'is', 'not', 'and', 
        'or', 'pass', 'raise', 'with', 'as', 'None', 'True', 'False', 'yield', 'async', 'await'
    ]
    for kw in keywords:
        line = re.sub(r'\b(' + kw + r')\b', r'<font color="#7c3aed"><b>\1</b></font>', line)
        
    # Highlight decorators or functions
    line = re.sub(r'(@\w+(\.\w+)?)', r'<font color="#b45309">\1</font>', line)
    
    # Reassemble
    if comment:
        line = f"{line}{comment}"
    return line

def highlight_html_line(line):
    # Escape HTML first
    line = html.escape(line)
    
    # Preserve double spaces
    line = line.replace('  ', '&nbsp;&nbsp;')
    
    # Highlight comments
    if '&lt;!--' in line or '--&gt;' in line:
        return f'<font color="#64748b">{line}</font>'
    if '//' in line:
        parts = line.split('//', 1)
        return f'{parts[0]}<font color="#64748b">// {parts[1]}</font>'
        
    # Highlight HTML tag wrappers and names
    line = re.sub(r'(&lt;/?\w+.*?&gt;)', r'<font color="#2563eb">\1</font>', line)
    
    # Highlight JS keywords
    js_keywords = ['const', 'let', 'var', 'function', 'await', 'async', 'return', 'if', 'else', 'document', 'window', 'fetch', 'localStorage']
    for kw in js_keywords:
        line = re.sub(r'\b(' + kw + r')\b', r'<font color="#7c3aed"><b>\1</b></font>', line)
        
    return line

# --- Code line wrapper with Syntax Highlighting ---
def wrap_code_text_with_highlight(code_content, file_type='python', limit=85):
    wrapped_lines = []
    for line in code_content.splitlines():
        # Handle indents
        indent = len(line) - len(line.lstrip())
        indent_str = "&nbsp;" * indent
        stripped_line = line.strip()
        
        # Apply Highlight
        if file_type == 'python':
            highlighted = highlight_python_line(stripped_line)
        else:
            highlighted = highlight_html_line(stripped_line)
            
        if len(stripped_line) <= limit:
            wrapped_lines.append(f"{indent_str}{highlighted}")
        else:
            # Wrap long code lines
            chunks = []
            for i in range(0, len(stripped_line), limit):
                chunks.append(stripped_line[i:i+limit])
                
            # Re-apply highlight to each chunk
            highlighted_chunks = []
            for idx, c in enumerate(chunks):
                if file_type == 'python':
                    hc = highlight_python_line(c)
                else:
                    hc = highlight_html_line(c)
                highlighted_chunks.append(hc)
                
            sub_indent_str = indent_str + "&nbsp;&nbsp;<font color='#94a3b8'>└─</font>&nbsp;"
            wrapped_lines.append(f"{indent_str}{highlighted_chunks[0]}")
            for c in highlighted_chunks[1:]:
                wrapped_lines.append(f"{sub_indent_str}{c}")
                
    return "<br/>".join(wrapped_lines)

# --- NumberedCanvas to support "Page X of Y" and Header Lines ---
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
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Sarabun", 8.5)
        self.setFillColor(HexColor("#475569")) # Slate 600
        
        # Header (Skip page 1)
        if self._pageNumber > 1:
            self.drawString(1.5 * cm, 28 * cm, "TutorMatch AI - เอกสารซอร์สโค้ดและคำอธิบายระบบ")
            self.setStrokeColor(HexColor("#cbd5e1"))
            self.setLineWidth(0.5)
            self.line(1.5 * cm, 27.7 * cm, 19.5 * cm, 27.7 * cm)
            
        # Footer (All pages)
        page_text = f"หน้า {self._pageNumber} จาก {page_count}"
        self.drawRightString(19.5 * cm, 1 * cm, page_text)
        self.drawString(1.5 * cm, 1 * cm, "เอกสารอธิบาย Source Code ระบบจับคู่ติวเตอร์ด้วย AI & Web Biometrics")
        self.restoreState()

# --- Load File Contents safely ---
def read_project_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            return f.read()
    except Exception as e:
        return f"# Error loading file {filepath}: {str(e)}"

# --- PDF Generation Script ---
def build_story(styles):
    story = []
    
    # Text Styles
    style_title = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Sarabun-Bold',
        fontSize=24,
        leading=30,
        textColor=HexColor("#4f46e5"), # Indigo 600
        alignment=1, # Center
        spaceAfter=12
    )
    
    style_subtitle = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=12.5,
        leading=18,
        textColor=HexColor("#475569"),
        alignment=1,
        spaceAfter=40
    )
    
    style_h1 = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Heading1'],
        fontName='Sarabun-Bold',
        fontSize=16,
        leading=22,
        textColor=HexColor("#7c3aed"), # Violet 600
        spaceBefore=22,
        spaceAfter=12,
        keepWithNext=True
    )
    
    style_body = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=9.5,
        leading=15,
        textColor=HexColor("#334155"),
        spaceAfter=8
    )

    style_code = ParagraphStyle(
        'Code_Custom',
        parent=styles['Code'],
        fontName='Sarabun', # Use Sarabun so comments in Thai display correctly
        fontSize=7.5,
        leading=11.5,
        textColor=HexColor("#0f172a"),
        backColor=HexColor("#f8fafc"),
        borderColor=HexColor("#cbd5e1"),
        borderWidth=0.5,
        borderPadding=12,
        spaceBefore=8,
        spaceAfter=15
    )

    # Styles for table cells to parse Paragraph correctly
    style_table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Sarabun-Bold',
        fontSize=9.5,
        leading=13,
        textColor=HexColor("#0f172a")
    )
    style_table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Sarabun',
        fontSize=9,
        leading=13,
        textColor=HexColor("#334155")
    )

    # --- Cover Page ---
    story.append(Spacer(1, 3 * cm))
    story.append(Paragraph(thai_text("TutorMatch AI - Source Code Documentation"), style_title))
    story.append(Paragraph(thai_text("เอกสารรวบรวมซอร์สโค้ดและคำอธิบายโค้ดโครงการอย่างละเอียดทุกไฟล์"), style_subtitle))
    story.append(Spacer(1, 1.5 * cm))
    
    # Files Table Info wrapped in Paragraph flowables
    table_files_data = [
        [
            Paragraph(thai_text("<b>ลำดับ</b>"), style_table_header), 
            Paragraph(thai_text("<b>ชื่อไฟล์ซอร์สโค้ด</b>"), style_table_header), 
            Paragraph(thai_text("<b>เทคโนโลยี / หน้าที่หลัก</b>"), style_table_header)
        ],
        [
            Paragraph(thai_text("1"), style_table_cell), 
            Paragraph(thai_text("database.py"), style_table_cell), 
            Paragraph(thai_text("Python SQLAlchemy (การกำหนด Database Session & Engine)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("2"), style_table_cell), 
            Paragraph(thai_text("models.py"), style_table_cell), 
            Paragraph(thai_text("SQLAlchemy Models (โครงสร้างตารางสมาชิก สแกนหน้า และนัดหมาย)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("3"), style_table_cell), 
            Paragraph(thai_text("schemas.py"), style_table_cell), 
            Paragraph(thai_text("Pydantic Schemas (ตัวแปรคำร้องขอและคำตอบกลับของ API)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("4"), style_table_cell), 
            Paragraph(thai_text("ai_engine.py"), style_table_cell), 
            Paragraph(thai_text("OpenCV Face Recognizer (LBPH) & Thai NLP (TF-IDF Matching)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("5"), style_table_cell), 
            Paragraph(thai_text("main.py"), style_table_cell), 
            Paragraph(thai_text("FastAPI Endpoints (แอปพลิเคชันหลังบ้านเชื่อมต่อหน้าเว็บ)"), style_table_cell)
        ],
        [
            Paragraph(thai_text("6"), style_table_cell), 
            Paragraph(thai_text("static/index.html"), style_table_cell), 
            Paragraph(thai_text("Vanilla HTML5/CSS/JS (สตรีมกล้องสแกนหน้า และ UI นัดเรียน)"), style_table_cell)
        ]
    ]
    
    t_files = Table(table_files_data, colWidths=[1.5 * cm, 4.5 * cm, 11.5 * cm])
    t_files.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#e2e8f0")),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#cbd5e1")),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(t_files)
    story.append(Spacer(1, 2 * cm))
    
    story.append(Paragraph(thai_text(
        "<b>คำอธิบายการจัดทำซอร์สโค้ด:</b><br/>"
        "โครงการนี้ได้รับการออกแบบเป็นสถาปัตยกรรม Web Service บนฐานการประมวลผลภาษาธรรมชาติภาษาไทย "
        "และตัวจดจำใบหน้าแบบออฟไลน์ (Local Biometrics) เพื่อแก้ไขปัญหาความยุ่งยากใน Python 3.14.3 "
        "โดยหลีกเลี่ยงการใช้ไลบรารีขนาดใหญ่อย่าง TensorFlow หรือ dlib "
        "และหันมาใช้อัลกอริทึมจดจำใบหน้าดั้งเดิมที่มีความสเถียรสูง (LBPH) ร่วมกับตัวจับภาพ Haar Cascade "
        "ทำให้ซอร์สโค้ดทั้งหมดมีน้ำหนักเบาและประมวลผลได้อย่างรวดเร็วในเครื่องคอมพิวเตอร์ทั่วไป"
    ), style_body))
    
    story.append(PageBreak())
    
    # --- Loop and document each source code file ---
    code_files = [
        {
            "name": "database.py",
            "path": "database.py",
            "type": "python",
            "desc": "ไฟล์นี้ทำหน้าที่กำหนดจุดเชื่อมต่อฐานข้อมูล SQLite (ผ่านไฟล์โลคอล `tutor_match.db`) "
                    "และกำหนดคลาส `SessionLocal` เพื่อสร้าง Session ในการอ่านและเขียนข้อมูล รวมถึง "
                    "ฟังก์ชัน `get_db` ซึ่งเป็น Dependency Injector เพื่อใช้เปิด-ปิดการทำงาน Session อัตโนมัติในแต่ละ API Request"
        },
        {
            "name": "models.py",
            "path": "models.py",
            "type": "python",
            "desc": "ไฟล์นี้ใช้สำหรับประกาศโครงสร้างตารางข้อมูลในระบบผ่านคลาส SQLAlchemy (ORM Models):<br/>"
                    "• <b>User:</b> ตารางเก็บรายชื่อสมาชิก กำหนดสิทธิ์เป็นนักเรียนหรือติวเตอร์<br/>"
                    "• <b>BiometricProfile:</b> ตารางเก็บภาพใบหน้าติวเตอร์และนักเรียนที่ลงทะเบียนไว้เป็นรหัส JPEG Base64<br/>"
                    "• <b>TutorProfile:</b> ตารางเก็บข้อมูลความรู้ความชำนาญ อัตราค่าสอน ช่องทางติดต่อ (LINE, เบอร์โทร, อีเมล) และข้อแนะนำนัดหมายเรียน<br/>"
                    "• <b>Appointment:</b> ตารางนัดหมายเรียนพิเศษที่นักเรียนส่งหาติวเตอร์ เก็บวันที่ เวลา โน้ต และสถานะอนุมัติ"
        },
        {
            "name": "schemas.py",
            "path": "schemas.py",
            "type": "python",
            "desc": "ไฟล์กำหนดโครงสร้างพารามิเตอร์ข้อมูล (Data Schema) ด้วย <b>Pydantic Models</b> "
                    "ใช้ในการตรวจสอบความถูกต้องและชนิดข้อมูลของข้อมูลขาเข้าและขาออกในระบบ API เช่น "
                    "ตรวจสอบความถูกต้องของ Base64 รูปภาพใบหน้า, โครงสร้างการสมัครสมาชิก, รายละเอียดประวัติติวเตอร์ "
                    "รวมถึงพารามิเตอร์ที่ใช้ยื่นจองเวลาเรียนและข้อกำหนดในการเรียกดูนัดหมายเรียน"
        },
        {
            "name": "ai_engine.py",
            "path": "ai_engine.py",
            "type": "python",
            "desc": "<b>ไฟล์สมองกล AI ของระบบแบ่งงานออกเป็น 2 อัลกอริทึมหลัก:</b><br/>"
                    "1. <b>ระบบวิเคราะห์ภาพสแกนใบหน้า (Face Recognition Engine):</b> ใช้ Haar Cascade ในการตีกรอบตรวจจับหาตำแหน่งใบหน้า "
                    "ครอปและย่อรูปใบหน้าให้อยู่ในขนาดมาตรฐาน 150x150 พิกเซลแปลงเป็นระดับสีเทา (Grayscale) "
                    "จากนั้นใช้ **LBPH Face Recognizer** ในการเรียนรู้ใบหน้าต้นฉบับและทำนายผลใบหน้าสแกนสดจากกล้อง "
                    "คำนวณออกมาเป็นคะแนนความต่างระยะห่าง (Distance confidence)<br/>"
                    "2. <b>ระบบจับคู่ติวเตอร์อัจฉริยะ (Tutor Matching Engine):</b> ทำการตัดคำภาษาไทยด้วย PyThaiNLP "
                    "และนำข้อความทั้งหมดมาเข้าสู่อัลกอริทึม **TF-IDF** เพื่อแปลงโปรไฟล์ผู้สอนและคำขอของผู้เรียนเป็นเวกเตอร์ "
                    "จากนั้นเปรียบเทียบด้วยสูตร **Cosine Similarity** เพื่อคำนวณคะแนนความเข้ากันได้ (%) และนำเงื่อนไขกรองงบประมาณ/วิชาหลักมาเป็นตัวกรองขั้นต้น"
        },
        {
            "name": "main.py",
            "path": "main.py",
            "type": "python",
            "desc": "ไฟล์ระบบ API หลักของแอปพลิเคชันหลังบ้านที่ถูกสร้างขึ้นด้วย <b>FastAPI</b> "
                    "ทำหน้าที่กำหนด API Route ทั้งหมดเพื่อทำงานร่วมกับหน้าจอมือถือ (LINE LIFF), "
                    "สร้างฐานข้อมูลอัตโนมัติในการรันครั้งแรก, ถอดรหัสรูปภาพ Base64 แปลงเป็นภาพไบนารีส่งไปประมวลผลที่ AI Engine "
                    "รวมถึงจัดเก็บและเรียกค้นนัดหมายเรียน และให้บริการไฟล์ Static หน้ากากเว็บของ LIFF Simulator"
        },
        {
            "name": "static/index.html",
            "path": "static/index.html",
            "type": "html",
            "desc": "หน้าจอเว็บหน้าบ้าน (Frontend UI) ที่ถูกพัฒนาแบบ Single Page Application เพื่อจำลองพฤติกรรมบนแอปพลิเคชัน LINE LIFF "
                    "ดีไซน์โทนสีสว่างด้วย Vanilla CSS และมี JavaScript จัดการตรรกะฝั่งผู้ใช้:<br/>"
                    "• เรียกเปิดกล้องหน้ามือถือ/คอมพิวเตอร์ผ่านเบราว์เซอร์ด้วย `mediaDevices.getUserMedia` และถ่ายรูปผ่าน Canvas<br/>"
                    "• มีฟังก์ชันจำลองสร้างข้อมูลติวเตอร์ 5 สาขาวิชาอัตโนมัติ เพื่อให้ผู้ใช้ทดสอบ Matching ได้ง่ายขึ้น<br/>"
                    "• แสดงผลคะแนน AI Match เปอร์เซ็นต์ พร้อมระบุ LINE ID และปุ่มกดจองเวลานัดหมายเรียนแบบ Interactive"
        }
    ]

    for f_info in code_files:
        story.append(Paragraph(thai_text(f"ไฟล์ที่เขียน: {f_info['name']}"), style_h1))
        
        # Beautiful left-accent card for file details
        callout_content = (
            f"<b>ตำแหน่งไฟล์ในโปรเจกต์:</b> <code>{f_info['path']}</code><br/>"
            f"<b>รายละเอียดการทำงาน:</b><br/>{f_info['desc']}"
        )
        t_callout = Table([[Paragraph(thai_text(callout_content), style_body)]], colWidths=[17.5 * cm])
        t_callout.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), HexColor("#f5f3ff")), # Soft purple
            ('LINELEFT', (0,0), (-1,-1), 3, HexColor("#7c3aed")), # Purple left accent line
            ('TOPPADDING', (0,0), (-1,-1), 10),
            ('BOTTOMPADDING', (0,0), (-1,-1), 10),
            ('LEFTPADDING', (0,0), (-1,-1), 15),
            ('RIGHTPADDING', (0,0), (-1,-1), 15),
        ]))
        story.append(t_callout)
        story.append(Spacer(1, 0.4 * cm))
        
        # Read file code content
        code_content = read_project_file(f_info['path'])
        
        # Format code content
        if f_info['name'] == "static/index.html":
            # HTML is long. Let's show first 120 lines and last 120 lines to keep document compact but complete
            lines = code_content.splitlines()
            if len(lines) > 240:
                short_code = "\n".join(lines[:120]) + "\n\n... [โค้ดส่วนการแสดงผลและสไตล์ชีทเพิ่มเติม ละไว้ในเอกสารเพื่อความกระชับ] ...\n\n" + "\n".join(lines[-120:])
                code_html = wrap_code_text_with_highlight(short_code, file_type='html')
            else:
                code_html = wrap_code_text_with_highlight(code_content, file_type='html')
        else:
            code_html = wrap_code_text_with_highlight(code_content, file_type='python')
            
        story.append(Paragraph(code_html, style_code))
        story.append(PageBreak())
        
    return story

def generate_source_pdf():
    pdf_filename = "source_code.pdf"
    fallback_filename = "source_code_updated.pdf"
    
    styles = getSampleStyleSheet()
    story = build_story(styles)
    
    # Try to write to source_code.pdf
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
        print("Beautiful source code PDF generation completed.")
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
        print(f"Beautiful source code PDF generation completed (saved as {fallback_filename}).")

if __name__ == "__main__":
    generate_source_pdf()
