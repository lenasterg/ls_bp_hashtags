/* The following code was generously provided by @imath <http://imath.owni.fr/>
 * Please do visit his website <http://imath.owni.fr/> for some awesome BuddyPress/WordPress plugins and tutorials
 */
jQuery(document).ready(function($){
    //when a hashtag is clicked
    $('.hashtag').click(function(){
            var hashtag = $(this).attr('href');
            //var hashtag='sss';
           $.post(ajaxurl, {
            action: "get_hashtag",
            cookie: encodeURIComponent(document.cookie),
            'hashtag': hashtag
        }, function() {
            return false;
        });
        return false;
})
});

