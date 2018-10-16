<h1>$Title</h1>

<% if $Locations.Count %>
    <div id="map_wrap"><div id="map_canvas"></div></div>
    <% if $MapDirections %>
        <div id="directions_wrap">
            <form id="frmDD" onsubmit="getDirections();return false;">
                <div class="field text" id="FirstName">
                    <label class="left">Destination:</label>
                    <div class="middleColumn">
                        <% if $Locations.Count > 1 %>
                            <select name="to_address" id="to_address" class="select">
                                <% loop $Locations %>
                                    <option value="$Address">{$Title}: $Address</option>
                                <% end_loop %>
                            </select>                            	
                        <% else %>
                            <input type="hidden" name="to_address" id="to_address" readonly value="<% with $Locations.First %>{$Title}: $Address<% end_with %>" />
                            <p><% with $Locations.First %>{$Title}: $Address<% end_with %></p>
                        <% end_if %>
                    </div>
                </div>
                <div class="field text" id="FirstName">
                    <label class="left">Get Directions:</label>
                    <div class="middleColumn">
                        <input type="text" value="" name="from_address" id="from_address" class="text">
                    </div>
                </div>
                <div class="btn-toolbar">
                    <input type="submit" value="Go">
                </div>
                <div class="clear"></div>
            </form>
            <div id="directions_ajax">
            </div><!--directions_ajax-->
        </div><!--directions_wrap-->
    <% end_if %>
<% end_if %>
$Content