;(function($){
    $(document).ready(function(){

        function keen_comment_ajax_action(postId, RatingValue=null){
    
            var post_id = postId,
            rating = RatingValue,
            _container = $('[data-id='+post_id+']'),
            _result    = $('.result');
        
            jQuery.ajax({
               url: keen_rating_object.ajax_url,
               type: 'post',
               data: {
                   action: 'keen_rating_ajax_display_data',
                   postid: post_id,
                   rating: rating,
               },
               beforeSend: function(){
                   console.log('submitting ', post_id, rating );
               },
               success: function(data){ 
                   console.log('data', data); 
                   _container.html(data.star);
                   _result.text('('+ data.user +')');
                //    console.log(data.resultROW)
                //    console.log(data.rating)
                if(data!=null){
                    alert(data);
                }
               }
   
            });  
        }
        
        // load rating on page load
        // keen_comment_ajax_action();
        
        // load rating on click
        $('.rating-container span').on('click', function(){
            var rating = $(this).index() + 1,
                post_id = $(this).parent().data('id');
            console.log(rating, post_id);
            keen_comment_ajax_action(post_id, rating)
        });


    });
})(jQuery);