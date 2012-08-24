function updateMessages(address)
{
    if (address === undefined) {
        address = '?show=' + $("#messages > article").length;
    }
    $.ajax({
        url: address,
        cache: false,
        success: function(html) {
            $("#messages").html(html);
        }
    });
}

$(document).ready(function() {
    setInterval('updateMessages()', 5000);

    $(".ajax").live('click', function(event) {
        $.get(this.href, '', updateMessages());
        event.preventDefault();
    });

    $("#moreButton").live('click', function(event) {
        updateMessages(this.href);
        event.preventDefault();
    });

    $("#sendButton").bind('click', function(event) {
        if ($("#regularInput").val() != undefined) {
            return true;
        }
        $.post("?action=add", $("#mainForm").serialize(), function() {
            $("#regularTextarea").val('');
            updateMessages();
        });
        return false;
    });
});