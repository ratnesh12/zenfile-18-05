<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>UploadiFive Test</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo base_url() ?>assets/uploadify/jquery.uploadify.min.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>assets/uploadify/uploadify.css">
    <style type="text/css">
        body {
            font: 13px Arial, Helvetica, Sans-serif;
        }
    </style>
</head>

<body>
<h1>Uploadify Demo</h1>
<form>
    <div id="queue"></div>
    <input id="file_upload" name="file_upload" type="file" multiple="true">
</form>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';


    <?php $timestamp = time();?>
    $(function() {
        $('#file_upload').uploadify({
            'debug':true,
            'auto':true,
            'swf': base_url + 'assets/uploadify/uploadify.swf',
            'uploader': 'http://zen/client/upload/do_upload',
            'formData'      : {'associate_id' : 'someValue'},
            'fileTypeExts':'*.jpg;*.bmp;*.png;*.tif',
            'fileTypeDesc':'Image Files (.jpg,.bmp,.png,.tif)',
            'fileSizeLimit':'2MB',
            'fileObjName':'userfile',
            'buttonText':'Select Photo(s)',
            'multi':true,
            'removeCompleted':false,
            'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
            } ,
            'onUploadSuccess' : function(file, data, response) {
                alert('The file ' + file.name + ' was successfully uploaded with a response of ' + response + ':' + data);
            }
        });
    });
</script>
</body>
</html>