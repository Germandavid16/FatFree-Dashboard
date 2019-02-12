<? if ($chosen) { ?>

	<? if ($count) { ?>
	<div class="toolbar">    

		<form class="item downloadForm" method="post">
			<input type="hidden" name="token" value="<?=$CSRF?>">
			<input type="hidden" name="download" value="1">
		</form>
		
		<form class="item clearForm" method="POST">
			<input type="hidden" name="token" value="<?=$CSRF?>">
			<input type="hidden" name="clear" value="1">
		</form>
		<div class="nav-center">
			<ul class="nav nav-pills nav-pills-primary nav-pills-icons" role="tablist">
				<li class="<? if ($diff['active']) { ?> active<?}?>">
					<a class="btn nav_btn" href="<?php echo $base.$diff['link'] ?>" id="compare">
						<i class="material-icons">compare</i> Compare
					</a>
				</li>
				<li class="<? if ($groupping['active']) { ?> active<?}?>">
					<a class="btn nav_btn"  href="<?php echo $base.$groupping['link'] ?>" id="grouping">
						<i class="material-icons">widgets</i> Grouping
					</a>
				</li>
				<li  class="<? if ($chart['active']) { ?> active <? } ?>">
					<a class="btn nav_btn" href="<?php echo $base.$chart['link'] ?>" id="chart">
						<i class="material-icons">timeline</i> &nbsp&nbsp Chart &nbsp&nbsp
					</a>
				</li>
				<li>
					<a class="btn nav_btn" id="to_excel">
						<i class="material-icons">insert_drive_file</i> To Excel
					</a>
				</li>
				<li>
					<a class="btn nav_btn" id="clear">
						<i class="material-icons">delete</i> &nbsp&nbsp Clear &nbsp&nbsp
					</a>
				</li>
			</ul>
		</div>
	</div>

	<div class="rosterBar">
		<div class="rosterBarItem totalCount">
			<div class="text-center">
				<p style="display: inline-block;">Total <span><?=$rosterMonth?> <?=$rosterType?></span> members: <span class="count"><?=$count?></span></p>
			</div>
		</div>
		<? if ($diff['active']) { ?>
		<div class="rosterBarItem diffHelp">
			<table class="mg_auto">
				<tr>
					<td class="diffHelpChanged">
						<span class="value">New value</span>
						<span class="valueOld">Old value</span>
					</td>
					<td class="diffHelpAdded">
						<div class="value">New patient</div>
					</td>
					<td class="diffHelpDeleted">
					</td>
				</tr>
			</table>
		</div>
		<? } ?>


		<?php if ($groups) { ?>
		<div class="rosterBarItem groupSections">
			<table class="mg_auto">
				<tr>
					<? foreach ($groups as $key => $sub_group) { ?>
					<td class="section">
						<div class="caption<? if ($sub_group['link']['active']) { ?> active<? } ?>">
							<a class="item" href="<?=$sub_group['link']['link']?>">
								<?=$sub_group['group_name']?><span class="count">(<?=$sub_group['total']?>)</span>:
							</a>
						</div>
						<div class="groups">
							<? foreach ($sub_group['fields'] as $instance_fields) { ?>
								<? foreach ($instance_fields as $g_field) { ?>
								<div class="group<? if ($g_field['link']['active']) { ?> active<? } ?>">
									<a class="item" href="<?=$g_field['link']['link']?>"><?=$g_field['title']?><span class="count">(<?=$g_field['count']?>)</span></a>
								</div>
								<? } ?>
							<? } ?>
						</div>
					</td>
					<? } ?>
					<td class="section">
						<div class="caption<? if ($deleted_link['active']) { ?> active<? } ?>">
							<a class="item" href="<?=$deleted_link['link']?>">
								Deleted Patients<span class="count">(<?=$del_grouptotal?>)</span>:
							</a>
						</div>
						<div class="groups">
							<? foreach ($deleted_header as $key => $d_header) { ?>
								<div class="group<? if ($d_header['link']['active']) { ?> active<? } ?>">
									<a class="item" href="<?=$d_header['link']['link']?>"><?=$d_header['title']?><span class="count">(<?=$d_header['count']?>)</span></a>
								</div>
							<? } ?>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<?php } else if ($fieldsView) { ?>
		<div class="rosterBarItem groupSections">
			<table class="mg_auto">
				<tr>
					<? foreach ($fieldsView as $caption => $section) { ?>
					<td class="section">
						<div class="caption"><?=$caption?>:</div>
						<div class="groups">
							<? foreach ($section as $group) { ?>
							<div class="group<? if ($group['active']) { ?> active<? } ?>">
								<a class="item" href="<?=$group['link']?>"><?=$group['value']?><span class="count">(<?=$group['count']?>)</span></a>
							</div>
							<? } ?>
						</div>
					</td>
					<? } ?>
				</tr>
			</table>
		</div>
		<? } ?>
	</div>


	<? if ($chart_data) { ?>
		<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto; margin-bottom: 30px "></div>
		<div id="pie_chart_month" style="min-width: 310px; height: 400px; margin: 0 auto; margin-bottom: 30px;"></div>
		<div id="line_chart_year" style="min-width: 310px; height: 450px; margin: 0 auto"></div>

	<? } else if ($groups) { ?>
		<table class="dataTable">
			<tr class="head">
				<td>Group</td>
				<td>Insurance</td>
				<? foreach ($group_headers as $key => $header) { ?>
					<td data-id="<?=$header['id']?>">
						<?=$header['title']?>
					</td>
				<? } ?>
			</tr>
			<? foreach ($group_data as $sub_data) { ?>
			<tr>
				<? foreach ($sub_data as $info): ?>
					<td class="">
						<div class="value"><?=$info?></div>
					</td>
				<? endforeach; ?>
			</tr>
			<? } ?>
		</table>

	<? } else if ($rows) {?>
		<table class="dataTable<? if ($diff['active']) { ?> dataTableDiff<? } ?>">
			<tr class="head">
			<? foreach ($fields as $field) { ?>
				<? if ($field['active']) { ?>
				<td data-id="<?=$field['id']?>">
					<?=$field['title']?>
				</td>
				<? } ?>
			<? } ?>
			</tr>

			<? foreach ($rows as $row) { ?>
			<tr>
				<? foreach ($fields as $field) { ?>
					<? if ($field['active']) { ?>
						<? $cell = $row['fields'][$field['position']] ?>        
						<td class="<?=$cell['class']?>">
							<div class="value"><?=$cell['value']?></div>
							<? if ($cell['class'] == 'changed') { ?>
							<div class="valueOld" title="Old value"><?=$cell['value_old']?></div>
							<?}?>
						</td>
					<? } ?>
				<? } ?>
			</tr>
			<? } ?>
		</table>

	<? }  else { ?>
		<div class="contentMessage">
			No results
		</div>
	<? } ?>

	<script>
	$('.clearForm').on('submit', function() {
		if (!confirm('Do you really want to clear current roster data?')) {
			return false;
		}
	});
	</script>

	<? } else {?>

	<form method="post" enctype="multipart/form-data">
	   <input type="hidden" name="token" value="<?=$CSRF?>">
	   <table class="formTable">
			<tr>
				<td>
					<label for="roster">Roster file:</label>
				<td>
			</tr>
			<tr>
				<td>
					<input type="file" name="roster" id="roster">                
					<button>Send</button>
				<td>
			</tr>
	   </table>
	</form>

	<? } ?> 
<? } else { ?>
<div class="row">
	<div id="combination" class="col-md-12" style="min-width: 310px; height: 450px; margin: 0 auto; margin-bottom: 30px;"></div>
	<div id="month_retention" class="col-md-12" style="min-width: 310px; height: 400px; margin: 0 auto; margin-bottom: 30px;"></div>
	<div id="year_retention" class="col-md-12" style="min-width: 310px; height: 450px; margin: 0 auto; margin-bottom: 30px;"></div>
	
</div>

<? } ?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/series-label.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<!-- Additional files for the Highslide popup effect -->
<script src="https://www.highcharts.com/media/com_demo/js/highslide-full.min.js"></script>
<script src="https://www.highcharts.com/media/com_demo/js/highslide.config.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="https://www.highcharts.com/media/com_demo/css/highslide.css" />


<script type="text/javascript">
	$('#to_excel').one('click', function (){
		$('.downloadForm').submit();
	});

	$('#clear').click(function() {
		$('.clearForm').submit();
	});

	// Gradient Color (Sand Theme of Highchart)
	'use strict';
	Highcharts.createElement('link', {
	    href: 'https://fonts.googleapis.com/css?family=Signika:400,700',
	    rel: 'stylesheet',
	    type: 'text/css'
	}, null, document.getElementsByTagName('head')[0]);
	Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
	    proceed.call(this);
	    this.container.style.background =
	        'url(http://www.highcharts.com/samples/graphics/sand.png)';
	});
	Highcharts.theme = {
	    colors: ['#f45b5b', '#8085e9', '#8d4654', '#7798BF', '#aaeeee',
	        '#ff0066', '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
	    chart: {
	        backgroundColor: null,
	        style: {
	            fontFamily: 'Signika, serif'
	        }
	    },
	    title: {
	        style: {
	            color: 'black',
	            fontSize: '16px',
	            fontWeight: 'bold'
	        }
	    },
	    subtitle: {
	        style: {
	            color: 'black'
	        }
	    },
	    tooltip: {
	        borderWidth: 0
	    },
	    legend: {
	        itemStyle: {
	            fontWeight: 'bold',
	            fontSize: '13px'
	        }
	    },
	    xAxis: {
	        labels: {
	            style: {
	                color: '#6e6e70'
	            }
	        }
	    },
	    yAxis: {
	        labels: {
	            style: {
	                color: '#6e6e70'
	            }
	        }
	    },
	    plotOptions: {
	        series: {
	            shadow: true
	        },
	        candlestick: {
	            lineColor: '#404048'
	        },
	        map: {
	            shadow: false
	        }
	    },

	    // Highstock specific
	    navigator: {
	        xAxis: {
	            gridLineColor: '#D0D0D8'
	        }
	    },
	    rangeSelector: {
	        buttonTheme: {
	            fill: 'white',
	            stroke: '#C0C0C8',
	            'stroke-width': 1,
	            states: {
	                select: {
	                    fill: '#D0D0D8'
	                }
	            }
	        }
	    },
	    scrollbar: {
	        trackBorderColor: '#C0C0C8'
	    },

	    // General
	    background2: '#E0E0E8'
	};
	Highcharts.setOptions(Highcharts.theme);
</script>



<? if ($chart_data) { ?>
<script type="text/javascript">
	var name = "<? echo $chart_data['data']['name'] ?>"
	var total = <? echo json_encode($chart_data['total']['data']) ?>;
	total = total.map(function(item){
		return parseInt(item, 10);
	});

	var data = <? echo json_encode($chart_data['data']['data']) ?>;
	data = data.map(function(item) {
		return parseInt(item, 10);
	})

	var month = <? echo json_encode($chart_month) ?>;

	var pie_chartdatas = [];
	<?php foreach ($chart_data['falias_month']['data'] as $key => $per_data) { ?>
		pie_chartdatas[<?=$key?>] = {name: '<?=$per_data["title"]?>', y:<?=$per_data["value"]?>};
	<? } ?>

	var year_linedata = [];
	<?php foreach ($chart_data['falias_year'] as $key => $year_data) { ?>
		var y_data = <? echo json_encode($year_data["data"]) ?>;
		y_data = y_data.map(function(item) {
			return parseInt(item, 10);
		})
		year_linedata[<?=$key?>] = {name: '<?=$year_data['title']?>', data: y_data}
	<? } ?>

	var status_name = '<?=$chart_data['status_name']?>';
	$(document).ready(function() {
		Highcharts.setOptions({
		    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
		        return {
		            radialGradient: {
		                cx: 0.5,
		                cy: 0.3,
		                r: 0.7
		            },
		            stops: [
		                [0, color],
		                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
		            ]
		        };
		    })
		});
		Highcharts.chart('container', {
		    chart: {
		        type: 'spline'
		    },
		    title: {
		        text: 'Combination Chart with Total'
		    },
		    subtitle: {
		        text: 'Insurance: ' + name
		    },
		    xAxis: {
		        categories: month,
		        gridLineWidth: 1,
		    },
		    yAxis: {
		        title: {
		            text: 'Patients'
		        }
		    },
		    tooltip: {
		        shared: true,
		        crosshairs: true
		    },
		   	plotOptions: {
		        series: {
		            cursor: 'pointer',
		            point: {
		                events: {
		                    click: function (e) {
		                        hs.htmlExpand(null, {
		                            pageOrigin: {
		                                x: e.pageX || e.clientX,
		                                y: e.pageY || e.clientY
		                            },
		                            headingText: this.series.name,
		                            maincontentText: this.category + ':<br/> ' +
		                                this.y + ' patients',
		                            width: 200
		                        });
		                    }
		                }
		            },
		            marker: {
		                lineWidth: 1
		            }
		        }
		    },
		    series: [{
		        name: 'Total',
		        data: total
		    }, {
		        name: name,
		        data: data
		    }]
		});

		// Build Per Insurance Month Pie Chart
		Highcharts.chart('pie_chart_month', {
		    chart: {
		        plotBackgroundColor: null,
		        plotBorderWidth: null,
		        plotShadow: false,
		        type: 'pie'
		    },
		    title: {
		        text: name + ' ' + status_name + ' <?=$chart_data['falias_month']['date']?>'
		    },
		    tooltip: {
		        pointFormat: '{series.name}: <b>{point.y} PT</b>'
		    },
		    plotOptions: {
		        pie: {
		            allowPointSelect: true,
		            cursor: 'pointer',
		            dataLabels: {
		                enabled: true,
		                format: '<b>{point.name}</b>: {point.y} PT',
		                style: {
		                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
		                },
		                connectorColor: 'silver'
		            }
		        }
		    },
		    series: [{
		        name: 'Patients',
		        data: pie_chartdatas
		    }]
		});

		Highcharts.chart('line_chart_year', {
		    chart: {
		        type: 'spline'
		    },
		    title: {
		        text: name + ' Yearly'
		    },
		    subtitle: {
		        text: 'Insurance: ' + name
		    },
		    xAxis: {
		        categories: month,
		        gridLineWidth: 1,
		    },
		    yAxis: {
		        title: {
		            text: 'Patients'
		        }
		    },
		    tooltip: {
		        shared: true,
		        crosshairs: true
		    },
		   	plotOptions: {
		        series: {
		            cursor: 'pointer',
		            point: {
		                events: {
		                    click: function (e) {
		                        hs.htmlExpand(null, {
		                            pageOrigin: {
		                                x: e.pageX || e.clientX,
		                                y: e.pageY || e.clientY
		                            },
		                            headingText: this.series.name,
		                            maincontentText: this.category + ':<br/> ' +
		                                this.y + ' patients',
		                            width: 200
		                        });
		                    }
		                }
		            },
		            marker: {
		                lineWidth: 1
		            }
		        }
		    },
		    series: year_linedata
		});
	});
</script>
<? } else { ?>
<script type="text/javascript">
	var colors = Highcharts.getOptions().colors;
	var red_color = colors[0];
	colors[0] = colors[1];
	colors[1] = "#FFA310";
	colors[2] = red_color;

	Highcharts.setOptions({
	    colors: Highcharts.map(colors, function (color) {
	        return {
	            radialGradient: {
	                cx: 0.5,
	                cy: 0.3,
	                r: 0.7
	            },
	            stops: [
	                [0, color],
	                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
	            ]
	        };
	    })
	});

	var month = <? echo json_encode($chart_month) ?>;

	Highcharts.chart('combination', {
	    title: {
	        text: 'ANNUAL PATIENT RETENTION'
	    },
	    xAxis: {
	        categories: month
	    },
	    labels: {
	        items: [{
	            html: 'Total',
	            style: {
	                left: '50px',
	                top: '18px',
	                color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
	            }
	        }]
	    },
	   
	    series: [{
	        type: 'column',
	        name: <?php echo json_encode($ave_chart['new']['name'])?>,
	        data: <?php echo json_encode($ave_chart['new']['data'])?>
	    }, {
	        type: 'column',
	        name: <?php echo json_encode($ave_chart['disenrolled']['name'])?>,
	        data: <?php echo json_encode($ave_chart['disenrolled']['data'])?>
	    }, {
	        type: 'column',
	        name: <?php echo json_encode($ave_chart['deleted']['name'])?>,
	        data: <?php echo json_encode($ave_chart['deleted']['data'])?>
	    }, {
	        type: 'pie',
	        name: 'patients',
	        data: [{
	            name: <?php echo json_encode($ave_chart['new']['name'])?>,
	            y: <?php echo json_encode($ave_chart['new']['total'])?>
	        }, {
	            name: <?php echo json_encode($ave_chart['disenrolled']['name'])?>,
	            y: <?php echo json_encode($ave_chart['disenrolled']['total'])?>
	        }, {
	            name: <?php echo json_encode($ave_chart['deleted']['name'])?>,
	            y: <?php echo json_encode($ave_chart['deleted']['total'])?>
	        }],
	        center: [100, 80],
	        size: 100,
	        showInLegend: false,
	        dataLabels: {
	            enabled: false
	        }
	    }]
	});

	var month_data = [];
	var year_data = [];
	<?php foreach ($retention_data['month_data'] as $key => $mon_data) { ?>
		month_data[<?=$key?>] = {name: '<?=$mon_data["title"]?>', y:<?=$mon_data["count"]?>};
	<? } ?>

	<?php foreach ($retention_data['year_data'] as $key1 => $y_data) { ?>
		year_data[<?=$key1?>] = {name: '<?=$y_data["title"]?>', y:<?=$y_data["count"]?>};
	<? } ?>


	// Build the chart
	Highcharts.chart('month_retention', {
	    chart: {
	        plotBackgroundColor: null,
	        plotBorderWidth: null,
	        plotShadow: false,
	        type: 'pie'
	    },
	    title: {
	        text: '<?=$retention_data["date"]?> INSURANCES TOTAL'
	    },
	    tooltip: {
	        pointFormat: '{series.name}: <b>{point.y} PT</b>'
	    },
	    plotOptions: {
	        pie: {
	            allowPointSelect: true,
	            cursor: 'pointer',
	            dataLabels: {
	                enabled: true,
	                format: '<b>{point.name}</b>: {point.y} PT',
	                style: {
	                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
	                },
	                connectorColor: 'silver'
	            }
	        }
	    },
	    series: [{
	        name: 'Patients',
	        data: month_data
	    }]
	});


	var series = [];
	<?php foreach ($retention_data['year_data'] as $y_key => $y_data) { ?>
		var data = <?=json_encode($y_data["count"])?>;
		data = data.map(function(item) {
			return parseInt(item, 10);
		})
		series[<?=$y_key?>] = {'name': "<?=$y_data['title']?>", 'data': data}
	<?php } ?>
	Highcharts.chart('year_retention', {
	    chart: {
	        type: 'spline'
	    },
	    title: {
	        text: 'ANNUAL INSURANCES TOTAL'
	    },
	    xAxis: {
	        categories: month,
	        gridLineWidth: 1,
	    },
	    yAxis: {
	        title: {
	            text: 'Patients'
	        }
	    },
	    tooltip: {
	        shared: true,
	        crosshairs: true
	    },
	   	plotOptions: {
	        series: {
	            cursor: 'pointer',
	            point: {
	                events: {
	                    click: function (e) {
	                        hs.htmlExpand(null, {
	                            pageOrigin: {
	                                x: e.pageX || e.clientX,
	                                y: e.pageY || e.clientY
	                            },
	                            headingText: this.series.name,
	                            maincontentText: this.category + ':<br/> ' +
	                                this.y + ' patients',
	                            width: 200
	                        });
	                    }
	                }
	            },
	            marker: {
	                lineWidth: 1
	            }
	        }
	    },
	    series: series
	});

</script>
<? } ?>

<script type="text/javascript">
	
</script>