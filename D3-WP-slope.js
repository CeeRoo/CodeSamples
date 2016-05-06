/* 
 *  D3 slopegraph - EDC.
 *  by Chris Rousseau 
 *  
 *  Produce a two-column slopegraph with D3 dynamically
 *  given a CSV file of education data.
 *
 *  Note the viz select area corresponds to the D3-WP plugin selection
 *  in the dash editor.
 */

HEIGHT = 75;
WIDTH = 500;

LEFT_MARGIN = 150;
RIGHT_MARGIN = 40;
TOP_MARGIN = 20;
BOTTOM_MARGIN = 50;

ORANGE = '#f26622';
GREY = '#7b7d7d';

var padding = 50;

var viz = d3.select(".wpd3-16001-0")
                .append('svg')
                .attr('id', 'viz')
                .attr('height', HEIGHT+140)
                //.style('border','1px solid red')
                .attr('width', WIDTH+50);


/**
 * Read in .csv rows and find min and max. There are two columns so
 * need to combine
 */
d3.csv('/wp-content/uploads/2016/02/slopechart-ref.csv', function(rows) {
  
  MinMax = getMinMax(rows);
  data = rows;
  
  /**
   * D3 is synchronous so functions outside of this callback
   * block get executed before callback is done. So, if setting
   * MinMax here and reference the var outside
   * of this callback, it will be undefined because the code
   * outside this block will execute before the callback is done.
   * once the request is made and before the data finishes loading which
   * is what triggers the callback. 
  
   * So, put function in here to make sure it runs after the data
   * is loaded. (http://stackoverflow.com/questions/9491885/csv-to-array-in-d3-js)
   */

  // Set Visual range given domain of data in file
  setYScale();
  
  // Add scaled Y coordinates for both left and right data file values
  data = buildYScales(data);
  
  /**
   * Build new temp array that has one row per data coordinate. Thus,
   * this will have twice as many elements. The reason for this is in 
   * case of data collision. With two similar data points, want to make
   * sure that visually, they do not overlap. If the data points are close,
   * then the subsequent data coordinates all need to be adjusted. 
   *
   * Array is sorted by data file value descending so that we work from the 
   * tallest vertical value down to the shortest. When adjusting, we ADD
   * to the scaled Y coordinate. Since Y of 0 is the vertical top, the higher
   * the scaled Y value, the lower the point appears on the graph. This is
   * inverse to the data file value. The smallest data file value will
   * have the highest scaled Y value. 
   */
  finalData = preProcessSlope(data);
  
  /**
   * SVG heading above the data
   */
    viz.append('svg:line')
	    .attr('x1', LEFT_MARGIN+75)
	    .attr('x2', WIDTH+25)
	    .attr('y1', TOP_MARGIN*2/3)
	    .attr('y2', TOP_MARGIN*2/3)
	    .attr('stroke', 'black')
	    .attr('opacity', .5)
	    
    viz.append('text')
	    .attr('x', WIDTH/2-25)
	    .attr('y', TOP_MARGIN/2)
	    .attr('text-anchor', 'left')
	    .text('Participated')
	    .attr('font-variant', 'small-caps');
	    
	  viz.append('text')
	    .attr('x', WIDTH-RIGHT_MARGIN*2)
	    .attr('y', TOP_MARGIN/2)
	    .attr('text-anchor', 'left')
	    .text('Did Not Participate')
	    .attr('font-variant', 'small-caps')
  
  /**
   * We now have pre-processed all the data
   * Now, let's draw the graph with D3.
   * 
   * finalData = array of objects with properties:
   *             label, left, left_coord, right, right_coord
   */
  
    // Left side text labels
    viz.selectAll('.left_labels')
	    .data(finalData).enter()
		  .append('text')
	
	    // properties to move text slightly to the right on open
	    .transition()
	    .delay(750) // before effect
	    .duration(1000) // during the effect
        .each("start", function() { 
            d3.select(this).attr('x', LEFT_MARGIN-100)
        })
  		.attr('x', LEFT_MARGIN+80)
  		.attr('y', function(d,i){
  			return d.left_coord
  		})
  		.attr('dy', '.35em')
  		.attr('font-size', 14)
  		.attr('font-weight', 'bold')
  		.attr('text-anchor', 'end')
  		.text(function(d,i){ 
  			return d.label
		})
		.attr('fill', 'black');
	
	 // Left side data points
    viz.selectAll('.left_values')
	    .data(finalData).enter()
		    .append('circle')
        .attr('r', 4)
        .attr('cx', function(d,i) {
            return LEFT_MARGIN+110;  
          })
        .attr('cy', function(d,i) {
            return d.left_coord 
        })
        .style('stroke', function(d,i) {
            return d.color
        })
        .style('fill', function(d,i) {
            return d.color
        });

    // Right side data points
    viz.selectAll('.right_values')
	    .data(finalData).enter()
		    .append('circle')
        .attr('r', 4)
        .attr('cx', function(d,i) {
            return WIDTH-RIGHT_MARGIN/2;  
          })
        .attr('cy', function(d,i) {
            return d.right_coord 
        })
         .style('stroke', function(d,i) {
            return d.color
        })
        .style('fill', function(d,i) {
            return d.color
        });
        
    // Lines between points
    viz.selectAll('.slopes')
	    .data(finalData).enter()
	    .append('line')
	    
	    // Start fade in transition
	    .transition()
	    .delay(750)
	    .duration(2000)
        .each("start", function() { 
            d3.select(this).style("opacity",0);
        })
        // End fade out transition
        .style("opacity",.8)
	    
  		.attr('x1', LEFT_MARGIN+110)
  		.attr('x2', WIDTH-RIGHT_MARGIN/2)
  		.attr('y1', function(d,i){
  			return d.left_coord
  		})
  		.attr('y2', function(d,i){
  			return d.right_coord
  		})
  		.attr('stroke', function(d,i) {
              return d.color
          })
  		.attr('stroke-width' , '4');

}); // end d3 function

/**
 * Read in rows and find min and max. There are two columns so
 * need to combine
 * 
 * INPUT:   ARRAY of OBJECTS- "rows"
 * OUTPUT:  ARRAY of two elements, min and the max value of the file data
 */
function getMinMax(rows) {
    
	var colCombined, min_side, max_side;
	var final_max = undefined;
	var final_min = undefined;
    
	for (var i = 0; i < rows.length; i += 1){
	    
    //colCombined = OBJECT with property and value for each csv column
		colCombined = rows[i];
		
		//Get minimum and Maximum value for each row of data
		min_side = Math.min(colCombined.P, colCombined.DNP);
		max_side = Math.max(colCombined.P, colCombined.DNP);
		
		//Set minimum if min value less than previous min
		if (final_min == undefined || min_side < final_min) {
		    final_min = min_side;
		}
		
		//Set maximum if max value more than previous max
		if (final_max == undefined || max_side > final_max) {
		    final_max = max_side;
		}
		
	}
	
	// Return array of the max and the min. 
	return [final_min, final_max ];
  
}

/**
 * set yScale to a domain and range.
 * need to combine
 */
function setYScale() {
    
    // note yScale is GLOBAL to be ref'd outside function 
    yScale = d3.scale.linear()
			.domain(MinMax)
			.range([TOP_MARGIN, HEIGHT-BOTTOM_MARGIN]);
}

function getYScale(d,i){
	return HEIGHT - yScale(d);
}

function buildYScales(d) {
/**
 * Add yScale_left_coord, yScale_right_coord properties to existing data array
 * 
 * INPUT:  ARRAY of data file(3 elements): label, P(participated), DNP(did not participated)
 * OUTPUT: ARRAY d (5 elements) with added y scaled coordinates for left(P) and right(DNP)
 */
    for (var i=0; i<d.length; i++) {
        d[i].yScale_left_coord = getYScale(d[i].P);
        d[i].yScale_right_coord = getYScale(d[i].DNP);
    }
    
    return d;
}

function preProcessSlope(d) {
/**
 *  Build new temp array that has one row per data coordinate. Thus,
 * this will have twice as many rows as the data file. 
 * 
 * The reason for this is in case of data collision. 
 * 
 * With two similar data points, want to make
 * sure that visually, they do not overlap. If the data points are close,
 * then the subsequent data coordinates all need to be adjusted. 
 * 
 * The new temp array is sorted by data file value descending so that we work from the 
 * tallest vertical value down to the shortest. When adjusting, we ADD
 * to the scaled Y coordinate. Since Y of 0 is the vertical top, the higher the Y
 * value, the LOWER the point appears visually on the graph. The higher
 * the scaled Y value, the lower the point appears on the graph. This is
 * inverse to the data file value. The smallest data file value will
 * have the highest scaled Y value. 
 *
 * INPUT: 
 *   - ARRAY of objects, each obj with 5 elems (added left and right Y scaled coordinates)
 * OUTPUT: 
 *   - ARRAY of objects, final pre-processed data with adjusted left and Y scales after
 *    determining visual data collisions)
 */

    var font_size = 15;
    
    var left = []; 
    var right = [];
    var datarow;
    
    /** 
     * Build two arrays for left and right sides. Then combine into one array
     * and sort by the data file values descending. Then figure out if there
     * are collisions. If data points are too close, re-adjust the Y scales   
     */
    for (var i=0; i<d.length; i++ ) {
        
        // datarow is row from csv data file
        datarow = d[i];
        
        // Left val = P (participate)
        // Right val = DNP (participate)
        leftval = datarow.P;
        rightval = datarow.DNP;
        
        /**
         * Push/append OBJECT to left and right arrays. In the push parameters,
         * we are defining the property names and values via object braces{}. 
         * 
         * Creating left and right arrays
         * which have object properties of:
         * 1) label(data file header)
         * 2) side (manually assigned 'left' or 'right')
         * 3) dataval (value from data file)
         * 4) yScaleCoord (value computed from yScale)
         */
        left.push({
            label:datarow.Standard, 
            side:'left', 
            dataval:parseInt(datarow.P),
            yScaleCoord:datarow.yScale_left_coord
        });
        
        right.push({
            label:datarow.Standard, 
            side:'right', 
            dataval:parseInt(datarow.DNP),
            yScaleCoord:datarow.yScale_right_coord
        });
    }// end build left, right arrays of objects
    
    // Concatenate left and right arrays.
    var both;
    both = left.concat(right);
    
    /**
     * Sort from low to high, then reverse so array is sorted 
     * descending by data file value. Need to reference the
     * property value name, in this case, dataval.
     */
    both.sort(function(a,b){
		if (a.dataval > b.dataval){
			return 1
		} else if (a.dataval < b.dataval) {
			return -1
		} else { 
			if (a.label > b.label) {
				return 1
			} else if (a.label < b.label) {
				return -1
			} else {
				return 0
			}
		}
	}).reverse();
    
    /**
     * Create object literal to temporarily store an object OF objects
     * for each category. The coordinates for each are going to be 
     * re-calculated in case of data collisions. Then we will move the object
     * into a new array of objects, which will be the FINAL storage location for 
     * all data before using SVG.
     */
    new_data = {};
    var label, side, dataval, yScaleCoord;
    var font_size = 15;
    
    /**
     * Both contains one row per data file point. For each row, we need to
     * compare the coordinates with the prior row to test for a display
     * collision. We build the new_data object as we go, so that only
     * one object per category is built and that each object has both the
     * left and right coordinates built.
     */
    for (var i=0; i< both.length; i++) {
        
        label = both[i].label;
        side = both[i].side;
        dataval = both[i].dataval;
        yScaleCoord = both[i].yScaleCoord;
        
        if (!new_data.hasOwnProperty(label)) {
            new_data[label] = {};
        }
        
        new_data[label][side] = dataval;
        
        /**
         * Here's the collision processing.
         * 
         * Compare the yScaled value with the prior coordinate. The prior
         * coordinate is a lower yScale, or a HIGHER vertical visual data point.
         * Works its way from the top down.
         */
        if (i>0) {
            
            if (yScaleCoord - font_size < both[i-1].yScaleCoord) {
                
                // THERE WAS A COLLISION!
                new_data[label][side + '_coord'] = yScaleCoord + font_size;
                
                /**
                 * Because there was a collision, every successive data
                 * point (again, moving from top to bottom), now needs to be 
                 * re-adjusted by the same amount ->font_size
                 */
                for (var j=i; j<both.length; j++) {
                    both[j].yScaleCoord = both[j].yScaleCoord + font_size; 
                }
                
            } else {
                new_data[label][side + '_coord'] = yScaleCoord;
            }
            
            /**
             * Now, we need to ensure that if there is a data point on the left
             * and the right sides with the same data file value, e.g. 64, that
             * visually, these have the SAME yScaled coordinate. The above 
             * coordinate adjustment can result in left and right side coords
             * being different for the same data file value. E.g., a data file
             * value of 64 for both left and right could result in, 
             * after above nested loop adjustment, in 
             * two different yScale coords of something like 145.58 and 160.58
             * 
             * Visually, we have to make sure the same data file value is the
             * same height on the left and the right. 
             */
             if (dataval === both[i-1].dataval && side!= both[i-1].side) {
                 new_data[label][side + '_coord'] = both[i-1].yScaleCoord;
             }
        }
        else {
            new_data[label][side + '_coord'] = yScaleCoord;
        }
        
    }// for both loop
    
    /**
     * new_data is now an Object of Objects.
     * Let's now transfer that to the FINAL array of objects
     * that will be used for the D3 SVG processing
     */
    finalDataArr = [];
    
    for (var label in new_data) {
        dataObj = new_data[label]; // dataObj is object (left, left_coord, right, right_coord)
        dataObj.label = label; // because the label is not a property of the obj yet
        
        if (label.indexOf("Curriculum") >= 0) {
            dataObj.color = ORANGE;
        }
        else {
            dataObj.color = GREY;
        }
    
        finalDataArr.push(dataObj);
    }
    
    return finalDataArr;

}// end preProcessSlope function
                
 