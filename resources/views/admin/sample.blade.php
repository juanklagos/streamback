<html>
<head>
  </head>
<body>
  <div class='video'>
  <script src="https://use.fontawesome.com/20603b964f.js"></script>

<!-- <script type="text/javascript" src="https://content.jwplatform.com/libraries/LJ361JYj.js"></script>

<script type="text/javascript">jwplayer.key = 'ypdL3Acgwp4Uh2/LDE9dYh3W/EPwDMuA2yid4ytssfI=';</script>
 -->

   <script src="{{asset('jwplayer/jwplayer.js')}}"></script>

<script type="text/javascript">jwplayer.key = 'ypdL3Acgwp4Uh2/LDE9dYh3W/EPwDMuA2yid4ytssfI=';</script>

<div id="myElement"></div>

<script type="text/javascript">

	    jwplayer("myElement").setup({
				image: "https://content.jwplatform.com/thumbs/xJ7Wcodt-720.jpg",
	    		aspectratio: "16:9",
				width: '100%',
				aspectratio: '16:9',
	    		autostart: false,
	    		// file : 'https://content.jwplatform.com/videos/xJ7Wcodt-cIp6U8lV.mp4',
				// file : 'http://184.72.239.149/vod/mp4:BigBuckBunny_115k.mov/playlist.m3u8',
				file: "http://206.189.96.133:1935/live/43_33/playlist.m3u8"
				// type: 'hls',
				// primary : flash

})
	</script>
</div>
  </body>
</html>