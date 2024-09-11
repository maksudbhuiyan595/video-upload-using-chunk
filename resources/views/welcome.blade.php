<!-- resources/views/upload.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Video Upload with Progress and AJAX</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #progress-bar {
            width: 0%;
            height: 20px;
            background-color: green;
        }
    </style>
     <style>
        video {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="row">
    </div>
    <h2>Upload Large Video</h2>
    <form id="video-upload-form" enctype="multipart/form-data">
        <input type="file" id="video" name="video" required accept="video/*">
        <button type="button" id="upload-btn">Upload</button>
    </form>
    <div id="progress-wrapper" style="display:none;">
        <div id="progress-bar"></div>
    </div>
    <br>
    <div id="status"></div>

   
    <script>
        $(document).ready(function () {
            $('#upload-btn').click(function () {
                const file = $('#video')[0].files[0];
                if (!file) {
                    alert("Please select a file.");
                    return;
                }

                const chunkSize = 1 * 1024 * 1024; // 1MB chunk size
                const totalChunks = Math.ceil(file.size / chunkSize);
                // console.log(totalChunks)
                const fileName = file.name;
                // console.log(fileName)
                let chunkNumber = 0;
                let progressBar = $('#progress-bar');
                let progressWrapper = $('#progress-wrapper');
                let status = $('#status');

                progressWrapper.show();
                progressBar.css('width', '0%');

                // Upload chunks one by one
                function uploadNextChunk() {
                    let start = chunkNumber * chunkSize;
                    let end = Math.min(start + chunkSize, file.size);
                    let chunk = file.slice(start, end);

                    let formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('fileName', fileName);
                    formData.append('chunkNumber', chunkNumber + 1);
                    formData.append('totalChunks', totalChunks);

                    $.ajax({
                        url: "{{ route('video.upload') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        xhr: function () {
                            let xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function (evt) {
                                if (evt.lengthComputable) {
                                    let percentComplete = Math.floor(((chunkNumber + 1) / totalChunks) * 100);
                                    progressBar.css('width', percentComplete + '%').text(percentComplete + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                chunkNumber++;
                                if (chunkNumber < totalChunks) {
                                    uploadNextChunk(); // Upload next chunk
                                } else {
                                    status.text("Upload complete!");
                                }
                            } else {
                                status.text("Upload failed.");
                            }
                        },
                        error: function () {
                            status.text("An error occurred during the upload.");
                        }
                    });
                }

                uploadNextChunk(); // Start the upload
            });
        });
    </script>
</body>
</html>
