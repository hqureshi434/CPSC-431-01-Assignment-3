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
                              this.username + ": " + this.message +    //Displays the username and message in the chatbubble 
                               '</div><div class="clearfix"></div>');
            } else {
                chatBubble = $('<div class="row bubble-recv" style="background-color: '+ this.color + ';">' + 
                               this.username + ": " + this.message +   //Displays the username and message in the chatbubble
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

$(document).ready(function(){ //When the user clicks the start button it will hide the username form and show the chat message form
    $("#startBtn").click(function(){
        $("#myUser").hide();
    });
    $("#startBtn").click(function () {
        $("#myMessage").show();
    });
});

$('#sendMessageBtn').on('click', function(event) { //Both the message and username are passed from the chat.html to the chat.php file. This function acts as a bridge between the two files.
    event.preventDefault();
    
    var message = $('#chatMessage').val();
    var userName = $('#userName').val();
    
    $.post('chat.php', {
        'message' : message,
        'userName' : userName
    }, function(result) {
        
        $('#sendMessageBtn').toggleClass('active'); //When the send message button is clicked the message and username are sent to chat.php
          
        if(!result.success) {
            alert("There was an error sending your message");
        } else {
            console.log("Message sent!");
            $('#chatMessage').val('');
        }
    });
    
});
