<% include DocumentHead %>

    <div class="print_me typography" style="padding:15px;">
        <div id="directions_head">
            <p><strong>Your Address:</strong> $StartAddress</p>
            <p><strong>Destination:</strong> $EndAddress</p>
            <p><strong>Driving Distance:</strong> $Distance</p>
            <p><strong>Driving Time:</strong> $Duration</p>
        </div><!--directions_head-->
        <div id="directions_list">
            $Steps
        </div><!--directions_list-->
    </div><!--print_me-->   

<% include DocumentFoot %>