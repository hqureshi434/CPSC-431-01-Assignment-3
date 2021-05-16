var pollServer = function() {
    $.get('chat.php', function(result) {
        
        if(!result.success) {
            console.log("Error polling server for new messages!");
            return;
        }
        
        $.each(result.messages, function(idx) {
            
            var chatBubble;
            
            if(this.sent_by == 'self') {
                chatBubble = $('<div class="row bubble-sent pull-right">' + 
                              this.username + ": " + this.message + 
                               '</div><div class="clearfix"></div>');
            } else {
                chatBubble = $('<div class="row bubble-recv">' + 
                               this.username + ": " + this.message + 
                               '</div><div class="clearfix"></div>');
            }
            
            $('#chatPanel').append(chatBubble);
        });
        
        setTimeout(pollServer, 5000);
    });
}

$(document).on('ready', function() {
    pollServer();
    
    $('button').click(function() {
        $(this).toggleClass('active');
    });
});

$(document).ready(function(){
    $("#startBtn").click(function(){
        $("#myUser").hide();
    });
    $("#startBtn").click(function () {
        $("#myMessage").show();
    });
});

$('#sendMessageBtn').on('click', function(event) {
    event.preventDefault();
    
    var message = $('#chatMessage').val();
    var userName = $('#userName').val();
    
    $.post('chat.php', {
        'message' : message,
        'userName' : userName
    }, function(result) {
        
        $('#sendMessageBtn').toggleClass('active');
          
        if(!result.success) {
            alert("There was an error sending your message");
        } else {
            console.log("Message sent!");
            $('#chatMessage').val('');
        }
    });
    
});
