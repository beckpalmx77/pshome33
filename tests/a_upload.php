<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <!--link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style type="text/css">
        .drop-area{
            width:100px; height:25px;
            border: 1px solid #999; text-align: center;
            padding:10px; cursor:pointer;
        }
        #thumbnail,div.removepic{
            display: flex;
            flex-wrap: wrap;
        }
        #thumbnail img{width:100px;height:100px;margin:5px;}
        #thumbnail div.removepic:hover{
            display:inline-block;
            border:1px dashed red;
        }
        canvas{border:1px solid red;}
    </style>
</head>
<body>

<br>
<div style="margin:auto;width:80%;">
    <h3> บันทึกข้อมูล</h3>
    <form class="form" id="myFrom" method="post" action="show_data.php"  role="form" enctype="multipart/form-data">
        <div class="form-group">
            <lable class="control-label">Name : </lable>
            <input type="text" autocomplete="off" class="form-control" name="name">
        </div>
        <div class="form-group">
            <lable class="control-label">Picture : </lable>
            <div id="upload" class="btn btn-info">
                Upload File
            </div>
            <div id="thumbnail"></div>
        </div>
        <button type="submit"  class="btn btn-primary">เพิ่มข้อมูล</button>
    </form>
    <br>

</div>


<script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
<script type="text/javascript" >
    $(function () {


        $("#upload").on("click",function(e){
            var lastFile = $(".file_upload:last").length;
            if(lastFile >= 0){
                if(lastFile == 0 || $(".file_upload:last").val()!=""){
                    var objFile= $("<input>",{
                        "class":"file_upload",
                        "type":"file",
                        "multiple":"true",
                        "name":"file_upload[]",
                        "style":"display:none",
                        change: function(e){
                            var files = this.files
                            showThumbnail(files)
                        }
                    });
                    $(this).before(objFile);
                    $(".file_upload:last").show().click().hide();
                }else{
                    $(".file_upload:last").show().click().hide();
                }
            }
            e.preventDefault();
        });

        function showThumbnail(files){

            //    $("#thumbnail").html("");
            for(var i=0;i<files.length;i++){
                var file = files[i]
                var fileName = file.name;
                var imageType = /image.*/
                if(!file.type.match(imageType)){
                    //     console.log("Not an Image");
                    continue;
                }

                var image = document.createElement("img");
                var wrapImg = document.createElement("div");
                var thumbnail = document.getElementById("thumbnail");
                wrapImg.className = 'removepic';
                wrapImg.setAttribute("data-file", fileName);
                image.file = file;
                wrapImg.appendChild(image);
                thumbnail.appendChild(wrapImg);

                var reader = new FileReader();
                reader.onload = (function(aImg){
                    return function(e){
                        aImg.src = e.target.result;
                    };
                }(image))

                var ret = reader.readAsDataURL(file);
                var canvas = document.createElement("canvas");
                ctx = canvas.getContext("2d");
                image.onload= function(){
                    ctx.drawImage(image,100,100)
                }
            } // end for loop

        } // end showThumbnail

        // ส่วนจัดการ การลบรูปที่ไม่ต้องการอัพโหลด จากการกดที่
        // หรือคลิกที่รูปที่ต้องการลบ
        $(document.body).on("click","div.removepic",function(){
            var removeFileName = $(this).data("file");
            var files = $("input.file_upload");
            var filesLength = files.length;
            for(var f = 0; f < filesLength; f++){
                var fileLists = files[f].files;
                if(fileLists !== undefined){
                    for(var fl = 0; fl < fileLists.length; fl++){
                        if(fileLists[fl].name === removeFileName) {
                            var removeImg = $("<input>",{
                                "type":"hidden",
                                "name":"remove_file[]",
                                "value":removeFileName
                            });
                            $(files[f]).after(removeImg);
                            break;
                        }
                    }
                };
            }
            $(this).remove();
        });


    });
</script>
</body>
</html>