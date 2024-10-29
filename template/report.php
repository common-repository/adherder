<?php
/*
Copyright 2011 Tristan Kromer, Peter Backx (email : tristan@grasshopperherder.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2>AdHerder Engagement Reports</h2>
	
	<?php 
		if($message) {
			echo '<div id="message" class="error">' . $message . '</div>';
		} 
		include(plugin_dir_path(__FILE__).'/../template/feedback.php');
	?>
	
	<div id="dashboard">
		<div style="display:none;">
					<div id="control-report"></div>
					<div id="control-status"></div>
		</div>
		<div>
			<div id="chart-report"></div>
		</div>
		<div>
			<div class="tablenav top">
			<div class="alignleft actions">
			<select>
				<option value="none">Bulk Actions</option>
				<option value="publish">Publish</option>
				<option value="pending">Unpublish</option>
				<option value="in_report">Include in report</option>
				<option value="not_in_report">Remove from report</option>
				<option value="clear_data">Clear data</option>
			</select>
			<button class="button-secondary apply-bulk" >Apply</button><br/>
			</div>
			<div class="alignleft actions">
			<select>
				<option value="report">Report</option>
				<option value="all">All</option>
				<option value="published">Published</option>
				<option value="unpublished">Unpublished</option>
			</select>
			<button class="button-secondary apply-filter" >Filter</button><br/>
			</div>
			</div>			
			
			<div id="chart-legend"></div>

			<div class="tablenav bottom">
			<div class="alignleft actions">
			<select>
				<option value="none">Bulk Actions</option>
				<option value="publish">Publish</option>
				<option value="pending">Unpublish</option>
				<option value="in_report">Include in report</option>
				<option value="not_in_report">Remove from report</option>
				<option value="clear_data">Clear data</option>
			</select>
			<button class="button-secondary apply-bulk" >Apply</button><br/>
			</div>
			</div>
		</div>
	</div>
	
	<form id="adherder_bulk_action_form" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<input type="hidden" name="adherder_bulk_ad_ids" id="adherder_bulk_ad_ids" />
		<input type="hidden" name="adherder_bulk_action" id="adherder_bulk_action" />
	</form>
	
<script type="text/javascript">
      google.load("visualization", "1.1", {packages:["corechart", "table", "controls"]});
      google.setOnLoadCallback(drawChart);
      var table, data, reportPicker, statusPicker;
      function drawChart() {
        data = new google.visualization.DataTable();
        data.addColumn('string', 'Ad id');
        data.addColumn('string', 'Title');
        data.addColumn('string', 'Status');
        data.addColumn('number', 'Impressions');
        data.addColumn('number', 'Clicks');
        data.addColumn('number', 'Conversion %');
        data.addColumn('number', 'Confidence %');
        data.addColumn('boolean', 'Relevant?');
        data.addColumn('string', 'In Report Data?');
        data.addColumn('number', 'min');
        data.addColumn('number', 'max');
        data.addColumn('number', 'opening');
        data.addColumn('number', 'closing');
        data.addRows(<?php echo count($reports); ?>);
        <?php 
        $count = 0;
        foreach($reports as $report) {
          echo "data.setValue(" . $count . ", 0, '" . $report->id . "');\n";
          echo "data.setValue(" . $count . ", 1, '" . $report->post_title . "');\n";
          echo "data.setValue(" . $count . ", 2, '" . $report->post_status . "');\n";
          echo "data.setValue(" . $count . ", 3,  " . $report->impressions . ");\n";
          echo "data.setValue(" . $count . ", 4,  " . $report->clicks . ");\n";
          echo "data.setValue(" . $count . ", 5,  " . $report->conversion . ");\n";
          echo "data.setValue(" . $count . ", 6,  " . $report->confidence . ");\n";
          echo "data.setValue(" . $count . ", 7,  " . $report->relevant . ");\n";
          echo "data.setValue(" . $count . ", 8, '" . ($report->in_report?"Yes":"No") . "');\n";
          echo "data.setValue(" . $count . ", 9,  " . $report->min . ");\n";
          echo "data.setValue(" . $count . ",10,  " . $report->max . ");\n";
          echo "data.setValue(" . $count . ",11,  " . $report->opening . ");\n";
          echo "data.setValue(" . $count . ",12,  " . $report->closing . ");\n";
          $count++;
        } ?>

		reportPicker = new google.visualization.ControlWrapper({
			'controlType': 'CategoryFilter',
			'containerId': 'control-report',
			'options'    : {
				'filterColumnLabel': 'In Report Data?',
				'ui': {
					'labelStacking'  : 'vertical',
					'allowTyping'    : false,
				}
			},
			'state': { 'selectedValues' : ['Yes'] }
		});

        statusPicker = new google.visualization.ControlWrapper({
          'controlType': 'CategoryFilter',
          'containerId': 'control-status',
          'options'    : {
            'filterColumnLabel': 'Status',
            'ui': {
              'labelStacking'  : 'vertical',
              'allowTyping'    : false,
            }
          }
        });

        var in_report_rows = data.getFilteredRows([{column: 8, value: 'Yes'}]);
        var chart = new google.visualization.ChartWrapper({
          'chartType'  : 'CandlestickChart',
          'dataTable'  : data, 
          'containerId': 'chart-report',
          'options'    : {
            'width'    : 400,
            'height'   : 240,
            'title'    : 'Ad engagement',
            'legend'   : 'none',
			'candlestick': {
			  'fallingColor' : { 
				'fill'   : '#FF0000', 
				'stroke' : '#FF0000', 
			  },
			  'risingColor' : { 
				'fill'   : '#00FF00', 
				'stroke' : '#00FF00', 
			  },
			},
          },
          'view'       : {
            'columns'  : [0, 9, 11, 12, 10],
            'rows'     : in_report_rows
          }
        });

        table = new google.visualization.ChartWrapper({
          'chartType'  : 'Table',
          'containerId': 'chart-legend',
          'options'    : {
            'allowHtml'     : true
          },
          'view'       : {
            'columns'  : [0, 1, 2, 3, 4, 5, 6, 7]
          }
        });

        new google.visualization.Dashboard(document.getElementById('dashboard'))
          .bind([reportPicker, statusPicker], [table])
          .draw(data);
          
  		chart.draw();
      }
      jQuery(document).ready(function($) {
		  $('.apply-bulk').click(function() {
			  var action = $(this).prev().val();
			  $('#adherder_bulk_action').val(action);
			  if("none" != action) {
				  var selection = table.getChart().getSelection();
				  if(selection.length != 0) {
					  var ids = "";
					  $.each(selection, function(i, obj) {
						  if(i!=0) {
							  ids += ',';
						  }
						  ids += table.getDataTable().getValue(obj.row,0);
					  });
					  $('#adherder_bulk_ad_ids').val(ids);
					  $('#adherder_bulk_action_form').submit();
				  }
			  }
		  });
		  
			$('.apply-filter').click(function() {
				var filter = $(this).prev().val();
				switch(filter) {
					case "report":
						reportPicker.setState({'selectedValues':['Yes']});
						statusPicker.setState({'selectedValues':[]});
						break;
					case "all":
						reportPicker.setState({'selectedValues':[]});
						statusPicker.setState({'selectedValues':[]});
						break;
					case "published":
						reportPicker.setState({'selectedValues':[]});
						statusPicker.setState({'selectedValues':['publish']});
						break;
					case "unpublished":
						reportPicker.setState({'selectedValues':[]});
						statusPicker.setState({'selectedValues':['pending']});
						break;
				}
				reportPicker.draw();
				statusPicker.draw();
			});
	  });
</script>
</div>
