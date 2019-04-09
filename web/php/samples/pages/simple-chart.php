<?php

    /* Include the `../src/fusioncharts.php` file that contains functions to embed the charts.*/
    include("../includes/fusioncharts.php");
?>
  <html>

    <head>
        <link rel="stylesheet" type="text/css" href="../FusionCharts/themes/fusioncharts.theme.fusion.css"></link>
        <title>FusionCharts | Simple Chart Using Array</title>
        <!-- FusionCharts Library -->
        <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
        <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script>
            <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.gammel.js"></script>
            <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.zune.js"></script>
            <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.carbon.js"></script>
            <script type="text/javascript" src="//cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.ocean.js"></script>
        
    </head>

    <body>

        <?php
  
 

$json = '{
  "chart": {
    "caption": "Apple (AAPL) Stock Price",
    "subcaption": "Last 6 Months",
    "numberprefix": "$",
    "pyaxisname": "Price (USD)",
    "theme": "fusion"
  },
  "categories": [
    {
      "category": [
        {
          "label": "Jan",
          "x": "1"
        },
        {
          "label": "Feb",
          "x": "22"
        },
        {
          "label": "Mar",
          "x": "41"
        },
        {
          "label": "Apr",
          "x": "62"
        },
        {
          "label": "May",
          "x": "83"
        }
      ]
    }
  ],
  "dataset": [
    {
      "data": [
        {
          "date": "Jan 02, 2018",
          "tooltext": "<b>Jan 02, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 170.16,
          "high": 172.3,
          "low": 169.26,
          "close": 170.9,
          "volume": "25555900",
          "x": 1
        },
        {
          "date": "Jan 03, 2018",
          "tooltext": "<b>Jan 03, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 172.53,
          "high": 174.55,
          "low": 171.96,
          "close": 170.87,
          "volume": "29517900",
          "x": 2
        },
        {
          "date": "Jan 04, 2018",
          "tooltext": "<b>Jan 04, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 172.54,
          "high": 173.47,
          "low": 172.08,
          "close": 171.67,
          "volume": "22434600",
          "x": 3
        },
        {
          "date": "Jan 05, 2018",
          "tooltext": "<b>Jan 05, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 173.44,
          "high": 175.37,
          "low": 173.05,
          "close": 173.62,
          "volume": "23660000",
          "x": 4
        },
        {
          "date": "Jan 08, 2018",
          "tooltext": "<b>Jan 08, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 174.35,
          "high": 175.61,
          "low": 173.93,
          "close": 172.98,
          "volume": "20567800",
          "x": 5
        },
        {
          "date": "Jan 09, 2018",
          "tooltext": "<b>Jan 09, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 174.55,
          "high": 175.06,
          "low": 173.41,
          "close": 172.96,
          "volume": "21584000",
          "x": 6
        },
        {
          "date": "Jan 10, 2018",
          "tooltext": "<b>Jan 10, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 173.16,
          "high": 174.3,
          "low": 173,
          "close": 172.92,
          "volume": "23959900",
          "x": 7
        },
        {
          "date": "Jan 11, 2018",
          "tooltext": "<b>Jan 11, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 174.59,
          "high": 175.49,
          "low": 174.49,
          "close": 173.9,
          "volume": "18667700",
          "x": 8
        },
        {
          "date": "Jan 12, 2018",
          "tooltext": "<b>Jan 12, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 176.18,
          "high": 177.36,
          "low": 175.65,
          "close": 175.69,
          "volume": "25418100",
          "x": 9
        },
        {
          "date": "Jan 16, 2018",
          "tooltext": "<b>Jan 16, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 177.9,
          "high": 179.39,
          "low": 176.14,
          "close": 174.8,
          "volume": "29565900",
          "x": 10
        },
        {
          "date": "Jan 17, 2018",
          "tooltext": "<b>Jan 17, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 176.15,
          "high": 179.25,
          "low": 175.07,
          "close": 177.69,
          "volume": "33888500",
          "x": 11
        },
        {
          "date": "Jan 18, 2018",
          "tooltext": "<b>Jan 18, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 179.37,
          "high": 180.1,
          "low": 178.25,
          "close": 177.85,
          "volume": "31193400",
          "x": 12
        },
        {
          "date": "Jan 19, 2018",
          "tooltext": "<b>Jan 19, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 178.61,
          "high": 179.58,
          "low": 177.41,
          "close": 177.05,
          "volume": "32425100",
          "x": 13
        },
        {
          "date": "Jan 22, 2018",
          "tooltext": "<b>Jan 22, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 177.3,
          "high": 177.78,
          "low": 176.6,
          "close": 175.6,
          "volume": "27108600",
          "x": 14
        },
        {
          "date": "Jan 23, 2018",
          "tooltext": "<b>Jan 23, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 177.3,
          "high": 179.44,
          "low": 176.82,
          "close": 175.64,
          "volume": "32689100",
          "x": 15
        },
        {
          "date": "Jan 24, 2018",
          "tooltext": "<b>Jan 24, 2018</b><br>Open: <b>$openDataValue</b><br>Close: <b>$closeDataValue</b><br>High: <b>$highDataValue</b><br>Low: <b>$lowDataValue</b><br>Volume: <b>$volumeValue Units</b>",
          "open": 177.25,
          "high": 177.3,
          "low": 173.2,
          "close": 172.85,
          "volume": "51105100",
          "x": 16
        }
      ]
    }
  ],
  "vtrendlines": [
    {
      "line": [
        {
          "startvalue": "10",
          "color": "#5D62B5",
          "displayvalue": "T1",
          "showontop": "0"
        },
        {
          "startvalue": "15",
          "color": "#5D62B5",
          "displayvalue": "T2",
          "showontop": "0"
        }
      ]
    }
  ]
}';
$columnChart = new FusionCharts("candlestick", "ex1", "100%", 800, "chart-1", "json",$json);
$columnChart->render();


?>

        <h3>Simple Chart Using Array</h3>
        <div id="chart-1">Chart will render here!</div>
        <br/>
        <br/>
        <a href="../index.php">Go Back</a>
    </body>

    </html>