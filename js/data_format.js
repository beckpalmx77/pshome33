function autoTab(obj){
    /* กำหนดรูปแบบข้อความโดยให้ _ แทนค่าอะไรก็ได้ แล้วตามด้วยเครื่องหมาย
    หรือสัญลักษณ์ที่ใช้แบ่ง เช่นกำหนดเป็น  รูปแบบเลขที่บัตรประชาชน
    4-2215-54125-6-12 ก็สามารถกำหนดเป็น  _-____-_____-_-__
    รูปแบบเบอร์โทรศัพท์ 08-4521-6521 กำหนดเป็น __-____-____
    หรือกำหนดเวลาเช่น 12:45:30 กำหนดเป็น __:__:__
    ตัวอย่างข้างล่างเป็นการกำหนดรูปแบบเลขบัตรประชาชน
    */
    let pattern=new String("_-____-_____-_-__"); // กำหนดรูปแบบในนี้
    let pattern_ex=new String("-"); // กำหนดสัญลักษณ์หรือเครื่องหมายที่ใช้แบ่งในนี้
    let returnText=new String("");
    let obj_l=obj.value.length;
    let obj_l2=obj_l-1;
    for(let i=0;i<pattern.length;i++){
        if(obj_l2==i && pattern.charAt(i+1)==pattern_ex){
            returnText+=obj.value+pattern_ex;
            obj.value=returnText;
        }
    }
    if(obj_l>=pattern.length){
        obj.value=obj.value.substr(0,pattern.length);
    }
}