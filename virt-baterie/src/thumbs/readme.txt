April 04 2009 (Rev7)

Revision 7

This should be called something like 2.0, After about 6 hours non-stop, I had to rewrite it from 0.

updated the original source code, to the newest, thanks to noponies.com

Fixed:
Flash 9 now displays all thumbs

Updated:
Removed Debug
Removed thumbMul

Added:
<overColor> when a thumb is clicked it will be overlayed by this color e.g.
<overColor>0x00ff00</overColor> will tint the thumb green, use HEX colors

TextOffset:
<textOffsetX> will set the Text on the X (Horizontal)position
<textOffsetY> will set the Text on the Y (Vertical) position 

you can place the text anywhere on the krpano stage.



There is still one minor setback you HAVE to set up the X And Y values for the thumb track in
<ThumbOffset> = X (Horizontal)
<ThumbOffsetY> = y (Vertical)

you will have to play and set this up, you can move the plugin all over the krpano stage and its relative to the Krpano plugin position

read the included gallery1.xml for all the options.




Enjoy and please, if you like this plugin and use it please consider buying me a beer ;) by paypal at: shantic@gmail.com





22 March 2009 (Rev4)
Fixed (hopefully) the no stop at end bug
for this I added a new option
<thumbMul>2</thumbMul>
the less the number the sooner they will stop at end.


22 March 2009
Fixed font bug, now it will load the correct one from the xml


19 MArch 2009

Fixed: Scroll end bug, now it will stop at the end of the thumbnails
Fixed: Loaded text now respects the XML optionf for font 


18 MArch 2009 

Added a few more options requested on the krpano forum, check the gallery1.xml for this options.

A brief explanation of all the options:


thumbW: Width of the Thumbnails
thumbH: Height of the Thumbnails
ThumbPadding: Space Between the thumbnails
ShownThumbs: how many thumbs are shown at the same time 
ThumbOffset: Unfortunately you will have to change this depending on how many thumbs you are showing to center them, and its a hit / miss thing, will try to make this better. for 5 thumbnails its -100 so just try it until it works to your liking.
InitialAlpha: transparency of the thumbnails
font_type: For the descriptive text
font_size: For the descriptive text
font_alpha: For the descriptive text
font_color: For the descriptive text

If you feel like it I do accept paypal donations on shantic@gmail.com


Example XML 

please add as many <pic> as thumbs needed.

where <about> is the text that is shown on top of the thumbnail
<panoToLoadXml> is the action to be called from your krpano XML


<?xml version="1.0" encoding="utf-8"?>

<images>
<thumboptions>
<ScrollSpeed>25</ScrollSpeed>
<thumbW>140</thumbW>
<ThumbPadding>20</ThumbPadding>
<ShownThumbs>5</ShownThumbs>
<ThumbOffset>-100</ThumbOffset>
<thumbH>70</thumbH>
<InitialAlpha>.2</InitialAlpha>
<font_type>Wide Latin</font_type>
<font_size>15</font_size>
<font_alpha>1</font_alpha>
<font_color>0xffffff</font_color>
</thumboptions>

    <pic>
        <thumb>../thumbs/nostalgia.jpg</thumb>
            <panoToLoadXml>s1</panoToLoadXml>
              <about><![CDATA[Text To Show]]></about>
       </pic>  
    
  </images>








Please add the plugin like this in your krpano xml file, make sure the path to the xml is correct :)

hopefully it will work!


also check the gallery1.xml file for options and how to add the thumbs and panos.



<plugin name="thumbnails" url="as3Thumbsforkrpano.swf?TheXML=./gallery1.xml" keep="true" align="bottomleft"/>


cs4 and cs3 fla included :)



original source code from
http://www.blog.noponies.com/archives/16

adated for krpano by me ;) 

Enjoy

Shanti Gilbert
shantic@gmail.com

