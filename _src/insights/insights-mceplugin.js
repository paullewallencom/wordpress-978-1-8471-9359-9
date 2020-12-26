// Insights tinyMCE 3 plugin 

(function() {
	
	tinymce.create('tinymce.plugins.Insights', {
	
		init : function(ed, url) {
			
		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceInsights');
			ed.addCommand('mceInsights', function() {				
				ed.windowManager.open({
					file : url + '/insights-popup.php',
					width : 650,
					height : 520,
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
				});
			});
			
			// Register a button
			ed.addButton('btnInsights', {
				title : 'Insights',
				cmd : 'mceInsights',
				image : url + '/i/button.gif'
			});

			
		},

		 // Returns information about the plugin as a name/value array.		 
		getInfo : function() {
			return {
				longname : 'Insights for WordPress',
				author : 'Vladimir Prelovac',
				authorurl : 'http://www.prelovac.com/vladimir',
				infourl : 'http://www.prelovac.com/vladimir/wordpress-plugins/insights',
				version : "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('insights', tinymce.plugins.Insights);
})();
