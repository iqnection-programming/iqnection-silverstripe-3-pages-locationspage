<h1>$Title</h1>
<% if Locations %>
    <div id="map_wrap"><div id="map_canvas"></div></div>
    <% if MapDirections %>
        <div id="directions_wrap">
            <form id="frmDD" onsubmit="getDirections();return false;">
                <div class="field text" id="FirstName">
                    <label class="left">Destination:</label>
                    <div class="middleColumn">
                        <% if NeedLocationsSelect %>
                            <select name="to_address" id="to_address" class="select">
                                <% control Locations %>
                                    <option value="$Address">{$Title}: $Address</option>
                                <% end_control %>
                            </select>                            	
                        <% else %>
                            <input type="hidden" name="to_address" id="to_address" readonly="readonly" value="<% control Locations.First %>{$Title}: $Address<% end_control %>" />
                            <p><% control Locations.First %>{$Title}: $Address<% end_control %></p>
                        <% end_if %>
                    </div>
                </div>
                <div class="field text" id="FirstName">
                    <label class="left">Get Directions:</label>
                    <div class="middleColumn">
                        <input type="text" value="" name="from_address" id="from_address" class="text">
                    </div>
                </div>
                <div class="Actions">
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