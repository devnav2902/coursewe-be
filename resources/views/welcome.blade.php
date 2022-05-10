<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta name="_token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="result" id="demo"></div>
    <form action="/save-file" method="POST">
        @csrf
        <input type="text" name="name-file">
        <button class="button-save" style="display:none" id="demo">Save</button>
    </form>
    <form method="POST" action="/handle-upload" enctype="multipart/form-data">
        @csrf
        <input id="file1" name="video" type="file" />
        <div class="progress-wrapper">
            <div id="progress-bar-file1" class="progress"></div>
        </div>
        <button type="button" onclick="postFile()">Upload File</button>
    </form>

    <script>
        function postFile() {
            var formdata = new FormData();

            formdata.append('video', document.getElementById('file1').files[0]);

            var request = new XMLHttpRequest();


            request.upload.addEventListener('progress', function(e) {
                var file1Size = document.getElementById('file1').files[0].size;
                console.log(file1Size);

                if (e.loaded <= file1Size) {
                    var percent = Math.round(e.loaded / file1Size * 100);
                    document.getElementById('progress-bar-file1').style.width = percent + '%';
                    document.getElementById('progress-bar-file1').innerHTML = percent + '%';
                }

                if (e.loaded == e.total) {
                    document.getElementById('progress-bar-file1').style.width = '100%';
                    document.getElementById('progress-bar-file1').innerHTML = '100%';
                }
            });

            request.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    console.log(request);
                    // Typical action to be performed when the document is ready:
                    document.getElementById("demo").innerHTML = request.responseText;
                    document.querySelector(".button-save").style.display = 'block';
                    document.querySelector('input[name="name-file"]').value = request.responseText;
                }
            };


            request.open('post', '/handle-upload', true);
            request.setRequestHeader('X-CSRF-Token', document.querySelector('meta[name="_token"]').content);
            request.timeout = 45000;
            request.send(formdata);
        }
    </script>
</body>

</html>
