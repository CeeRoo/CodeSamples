/*================================================================== 
 *
 * D3 Horizontal Stacked graph. 
 *
 * Displays % of teachers who have responded to variable # of standards surveys.
 * Number of surveys and percentages can be adjusted in the .csv
 * at any time and the D3 graph will display accordingly. 
 *  
 ==================================================================*/ 

var margin = {
    top: 20, 
    right: 20, 
    bottom: 30, 
    left: 40
};

var padding = 30;
var yaxis_shim = 240;

var width = 725;
var height = 200 - margin.top - margin.bottom;

/**
 * Vertical y range is ordinal
 *
 * 'rangeRoundBands' guarantees that range values and band width are integers
 */
var y = d3.scale.ordinal()
    .rangeRoundBands([0, height], .3); // The padding is typically range [0,1]

/**
 * #rangeBand() - 
 *       Returns the single value of each band width. When the scale’s range is configured with 
 *       rangeBands or rangeRoundBands, the scale returns the lower value for 
 *       the given input. The upper value can then be computed by offsetting 
 *       by the band width. If the scale’s range is set using 
 *       range or rangePoints, the band width is zero.
 */
var x = d3.scale.linear()
    .rangeRound([0, width-150-yaxis_shim/2]);

var color = d3.scale.ordinal()
    .range(["#3d414b", "#aebbc5", "#f26622"]);

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left")
    .outerTickSize(1);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom")
    .ticks(5);

var textshim = 5;

var svg = d3.select(".wpd3-16140-0")
    .append("svg")
    .attr("width", width )
    .attr("height", height + margin.top + margin.bottom + margin.left)
    .append("g")
    .attr("transform", "translate(" + yaxis_shim + "," + 80 + ")");

// Start processing the .csv file of data
d3.csv("/wp-content/uploads/2016/08/h-stacked-final3.csv", function(error, data) {
  if (error) throw error;

/**
 * 
 *  Ordinal scales have a discrete domain, such as a set of names or categories.
 *  Unlike quantitative values that can be compared numerically, subtracted or divided, 
 *  ordinal values are compared by rank. Letters are ordinal. E.g., in the alphabet, 
 *  A occurs before B, and B before C.
 *  
 *  var x = d3.scale.ordinal()
 *
 *  .domain(["A", "B", "C", "D", "E", "F"])
 *  .range([0, 1, 2, 3, 4, 5]);
 *
 *  The result of x("A") is 0, x("B") is 1, and so on.
 *   
 *  It would be tedious to enumerate the positions of each bar by hand, so 
 *  instead we can convert a continuous range into a discrete set of values 
 *  using rangeBands or rangePoints. The rangeBands method computes range values
 *  so as to divide the chart area into evenly-spaced, evenly-sized bands, 
 *  as in a bar chart.
 *  
 *  var x = d3.scale.ordinal()
 *   .domain(["A", "B", "C", "D", "E", "F"])
 *   .rangeBands([0, width]);
 *   
 *  If width is 960, x("A") is now 0 and x("B") is 160, and so on
 *  
 *  d3.keys returns property names of associative array.
 * 
 *  data[0] is first row of data under the column headings in the .csv.
 *   
 *  domain statement must be inside the csv callback loop because D3 is asynchronous and
 *  we can't process the domain without the data being first loaded.
 * 
 *  Domain matches a given RANGE. Domain: input data. Range: Output display.
 *  So for color, we've defined the color range: 7 colors. Each of these
 *  will match the 7 columns (not including the 8th 'state' column
 *  in the .csv) after the domain statement below. 
 * 
 *    In console type:
 *    color.range()
 *    -->["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"]
 *
 */
  color.domain(d3.keys(data[0])
                .filter(function(key) { 
                    return key !== "Category"; 
                })
                );

/**
 * 'data' is an array of objects. Each object is a row in the csv.
 * 
 * The map() method creates a new array with the results of calling a 
 * function for every array element. 'thename' can be made up var name.
 * It corresponds to the KEY name, which equates to the column headings.
 */
  var rowcount = 0;
  data.forEach(function(d) {
    
    /** 
     * y0 is the left data coordinate(raw from file). For   
     * each value in one row, the coordinate of the previous
     * column is added to the current column coordinate to 
     * get a starting left point on where to draw the next
     * section of the horizontal bar.
     * 
     * y1 is the rightmost coordinate (raw from file). So D3, for each
     * row, will use y0/y1 as the left/right corrdinates for width of one
     * section of the horizontal bar.
     */
    var y0 = 0;
    
    // coords is being created as an array of objects property of data.
    d.coords = color.domain().map(function(thename) { 
                                    return {
                                        rowcount: ++rowcount,
                                        name: thename, 
                                        y0: y0, 
                                        keyvalue: Math.round(d[thename]),
                                        y1: y0 += +d[thename]}; //coerce to number with +
                                    }
                                );
    
    // total is new primitive property. Adds up all coords from file.
    d.total = d.coords[d.coords.length - 1].y1;
    
  });

/**
 * y is an ORDINAL scale, so the domain won't be a min and max
 * range of values like the linear scale. It will be an array of
 * values, in this case an array of categories. 
 */
  y.domain(data.map(function(d) { 
                        return d.Category; 
                    })
           );
/**
 * x is a LINEAR scale so you need a min and max range of values.
 */
  x.domain([0, 100]);
      
  svg.append("g")
      .attr("class", "yaxis-hstacked")
      .call(yAxis)
      .append("text")
      .attr("transform", "translate(" + padding + ",0)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "e.nd");

/** 
 * Translate will use the scaled y ordinal scale function from above using 
 * the state name as a parm to determine the Y coordinate with 
 * which to move the horizontal bar. translate(x,y).
 */
  var studycat = svg.selectAll(".studycat")
      .data(data)
      .enter()
      .append("g")
      .attr("class", "g")
      .attr("transform", function(d) { 
          return "translate(0," + y(d.Category) + ")"; 
      });

  studycat.selectAll("rect")
      .data(function(d) { 
          return d.coords; 
      })
      .enter()
      .append("rect")
      /**
       * ordinal.rangeBand()
       * Returns the band width. When the scale’s range is configured with 
       * rangeBands or rangeRoundBands, the scale returns the lower value for 
       * the given input. The upper value can then be computed by offsetting 
       * by the band width. If the scale’s range is set using 
       * range or rangePoints, the band width is zero.
       */ 
      .attr("height", y.rangeBand())
      .attr("x", function(d) { 
          return x(d.y0); 
      })
      .attr("width", function(d) { 
          return x(d.y1) - x(d.y0); 
      })
      .style("fill", function(d) { 
          return color(d.name); 
      });

/** 
 * Text Labels
 */
  studycat.selectAll("text")
    .data(function(d) { 
       return d.coords; 
     })
    .enter()
	.append("text")
    // properties to move text slightly to the right on open
    .transition()
    .delay(100) // before effect
    .duration(1500) // during the effect
    .each("start", function() { 
        d3.select(this).attr('x', margin.left)
        .attr('fill', 'white');
    })
	.attr("x", function(d) { 
          return x(d.y0)+textshim; 
      })
	.attr('y', function(d){
		return 12; //y value from start of current g, not from top of entire viz
	})
	.attr('dy', '.35em')
	.attr('font-size', 12)
	.attr('font-weight', 'bold')
	.attr('text-anchor', 'end')
	.text(function(d,i){ 
		return d.keyvalue + "%"
	})
	.attr('fill', function(d) { 
	    if(d.name==="Some") {
	        return 'black';
	    }
	    else {
	        return 'white';
	    }
      })
	.attr('text-anchor', 'start');

  var legend = svg.selectAll(".legend")
      .data(color.domain().slice())
      .enter()
      .append("g")
      .attr("class", "legend")
      .attr("transform", function(d, i) { 
          return "translate(0,-25)"; 
      });

  legend.append("rect")
      .attr("x", function(d, i) {
        return i*100+40;
      })
      .attr("width", 25)
      .attr("height", 18)
      .style("fill", color);

  legend.append("text")
      .attr("x", function(d, i) {
        return i*100 + 70;
      })
      .attr("y", 9)
      .attr("dy", ".35em")
      .text(function(d) { 
          return d; 
      });
   
   
// Text title
svg.append("text")
        .attr("x", (width / 2 -135))             
        .attr("y", -50 )
        .attr("text-anchor", "middle")  
        .style("font-size", "16px") 
        .text("Percentage of teachers who participated in:");

// y axis title
svg.append("text")
        .attr("x", -9)             
        .attr("y", 0 )
        .attr("text-anchor", "end")  
        .style("font-size", "14px") 
        .style("font-style", "italic") 
        .text("Evaluation Standards");

});