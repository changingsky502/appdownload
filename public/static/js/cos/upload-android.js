var cos = new COS({
    // 必选参数
    getAuthorization: function (options, callback) {
        // 服务端 JS 和 PHP 例子：https://github.com/tencentyun/cos-js-sdk-v5/blob/master/server/
        // 服务端其他语言参考 COS STS SDK ：https://github.com/tencentyun/qcloud-cos-sts-sdk
        // STS 详细文档指引看：https://cloud.tencent.com/document/product/436/14048
        $.get('index.php?c=site&a=getUploadParam&type=app', {
            // 可从 options 取需要的参数
        }, function (data) {
            var credentials = data && data.credentials;
            if (!data || !credentials) return console.error('credentials invalid');
            callback({
                TmpSecretId: credentials.tmpSecretId,
                TmpSecretKey: credentials.tmpSecretKey,
                XCosSecurityToken: credentials.sessionToken,
                // 建议返回服务器时间作为签名的开始时间，避免用户浏览器本地时间偏差过大导致签名错误
                StartTime: data.startTime, // 时间戳，单位秒，如：1580000000
                ExpiredTime: data.expiredTime, // 时间戳，单位秒，如：1580000900
            });
        });
    }
});
$('#uploadBtn').click(function () {
    $('#file-selector').click();
})
// 监听选文件
document.getElementById('file-selector').onchange = function () {
    var file = this.files[0];
    if (!file) return;
    suffix = get_suffix(file.name)
    KEY = "app/" + random_string(10) + suffix
    if (file.size > 1024 * 1024) {
        cos.sliceUploadFile({
            Bucket: Bucket,
            Region: Region,
            Key: KEY,
            Body: file,
            onTaskReady: function (tid) {
                TaskId = tid;
                document.getElementById('ossfile').innerHTML += file.name + ' (' + getFileSize(file.size) + ') 进度：<span id="percent-txt">1%</span>';
                $('.progress').removeClass('hidden');
            },
            onHashProgress: function (progressData) {
                //   console.log('onHashProgress1', JSON.stringify(progressData));
            },
            onProgress: function (progressData) {
                if (progressData.percent != 1) {
                    $('.step2 .progress-bar').css('width', progressData.percent * 100 + '%');
                    $('#ossfile #percent-txt').text(progressData.percent * 100 + '%');
                } else {
                    $('.progress, #ossfile').addClass('hidden');
                }
            },
        }, function (err, data) {
            console.log(err || data);
            if (data) {
                $("input[name='android_link']").val('https://' + data.Location);
            }
        });
    } else {
        cos.putObject({
            Bucket: Bucket,
            Region: Region,
            Key: KEY,
            Body: file,
            onTaskReady: function (tid) {
                TaskId = tid;
                document.getElementById('ossfile').innerHTML += file.name + ' (' + getFileSize(file.size) + ') 进度：<span id="percent-txt">1%</span>';
                $('.progress').removeClass('hidden');
            },
            onHashProgress: function (progressData) {
                // console.log('onHashProgress', JSON.stringify(progressData));
            },
            onProgress: function (progressData) {
                if (progressData.percent != 1) {
                    $('.step2 .progress-bar').css('width', progressData.percent * 100 + '%');
                    $('#ossfile #percent-txt').text(progressData.percent * 100 + '%');
                } else {
                    $('.progress, #ossfile').addClass('hidden');
                }
            },
        }, function (err, data) {
            console.log(err || data);
            if (data) {
                $("input[name='android_link']").val('https://' + data.Location);
            }
        });
    }

};

function get_suffix(filename) {
    pos = filename.lastIndexOf('.')
    suffix = ''
    if (pos != -1) {
        suffix = filename.substring(pos)
    }
    return suffix;
}

function random_string(len) {
    len = len || 32;
    var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var maxPos = chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

//转换文件大小单位
function getFileSize(fileByte) {
    var fileSizeByte = fileByte;
    var fileSizeMsg = "";
    if (fileSizeByte < 1048576) fileSizeMsg = (fileSizeByte / 1024).toFixed(2) + "KB";
    else if (fileSizeByte == 1048576) fileSizeMsg = "1MB";
    else if (fileSizeByte > 1048576 && fileSizeByte < 1073741824) fileSizeMsg = (fileSizeByte / (1024 * 1024)).toFixed(2) + "MB";
    else if (fileSizeByte > 1048576 && fileSizeByte == 1073741824) fileSizeMsg = "1GB";
    else if (fileSizeByte > 1073741824 && fileSizeByte < 1099511627776) fileSizeMsg = (fileSizeByte / (1024 * 1024 * 1024)).toFixed(2) + "GB";
    else fileSizeMsg = "文件超过1TB";
    return fileSizeMsg;
}
