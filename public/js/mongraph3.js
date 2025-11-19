function legraph(data,env,d,f){

    //alert("apres "+d+" "+f);

  //  alert('test grand graph');

  if(d<0)d=0;

  if (f>=data.values.length) f=data.values.length;

    zoom=Math.floor((f-d)/1000);

    if(zoom==0)zoom++;









    var graph = document.getElementById("graph");

    var ctx = graph.getContext("2d");

    var tipCanvas = document.getElementById("tip");

    var tipCtx = tipCanvas.getContext("2d");



    var canvasOffset = $("#graph").offset();

    var offsetX = canvasOffset.left;

    var offsetY = canvasOffset.top;



 //   var graph;

    var xPadding = 50;

    var yPadding = 180;

    ctx.clearRect(0, 0, graph.width, graph.height);

    // Notice I changed The X values





    // define tooltips for each data point

    var dots = [];

    for(var i = 0; i < f-d; i +=zoom) {

        dots.push({

            x: getXPixel(data.values[i+d].X),

            y: getYPixel(data.values[i+d].Y),

            r: 4,

            rXr: 16,

            color: data.values[i+d].color,

            tip:data.values[i+d].X+"m "+data.values[i+d].nom+": "+data.values[i+d].Y+"k/h a "+data.values[i+d].heure

        });

    }





    // alert("avant "+d+" "+f);

     doo=d;

     foo=f;

     zoo=zoom;







    // request mousemove events

    $("#graph").mousemove(function(e){handleMouseMove(e);});



    // show tooltip when mouse hovers over dot

    function handleMouseMove(e){



      mouseX=parseInt(e.clientX-offsetX);

      mouseY=parseInt(e.clientY-offsetY);



      // Put your mousemove stuff here

      var hit = false;

      for (var i = 0; i < dots.length; i++) {

          var dot = dots[i];

          var dx =Math.abs(mouseX - dot.x);

        //  if(i==0)min=dx;else if(min>dx)min=dx;

          if (dx < 3) {

              tipCanvas.style.left = (dot.x) + "px";

              tipCanvas.style.top = (dot.y-40) + "px";

              document.getElementById("lepoint").style.left = (dot.x-7) + "px";

              document.getElementById("lepoint").style.background.color=dot.color;

              document.getElementById("lepoint").style.top = (dot.y-7) + "px";

              tipCtx.clearRect(0, 0, tipCanvas.width, tipCanvas.height);

              tipCtx.fillstyle=dot.color;

              tipCtx.fillstyle="bold";

              tipCtx.fillText(dot.tip, 5, 15);

              hit = true;

          }

      }

      if (!hit) { tipCanvas.style.left = "-2000px";

                  document.getElementById("lepoint").style.left = "-2000px";

                  }



    }

    // Returns the max Y value in our data list

    function getMaxY() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].Y > max) {

                max = data.values[i].Y;

            }

        }



        max += 10 - max % 10;

        max=65;

        return max;

    }



    // Returns the max X value in our data list

    function getMaxX() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].X > max) {

                max = data.values[i].X;

            }

        }



        // omited

      //max += 10 - max % 10;

        return max;

    }







    // Return the x pixel for a graph point

    function getXPixel(val) {

        // uses the getMaxX() function

         return ((graph.width - xPadding)/ (getMaxX()-data.values[d].X))*(val-data.values[d].X)+xPadding;

        // was

      //return ((graph.width - xPadding) / getMaxX()) * val + (xPadding * 1.5);

    }



    // Return the y pixel for a graph point

    function getYPixel(val) {

        return graph.height - (((graph.height - yPadding) / getMaxY()) * val) - yPadding;

    }



        graph = document.getElementById("graph");

        var c = graph.getContext('2d');



        c.lineWidth = 2;

        c.strokeStyle = '#333';

        c.font = 'italic 8pt sans-serif';

        c.textAlign = "center";



        // Draw the axises

        c.beginPath();

        c.moveTo(xPadding, 0);

        c.lineTo(xPadding, graph.height );

    //    c.lineTo(graph.width, graph.height - yPadding);

        c.stroke();

        c.beginPath();

        c.strokeStyle="#ddd";

        c.moveTo(xPadding, getYPixel(-3));

        c.lineTo(graph.width, getYPixel(-3));

        c.moveTo(xPadding, getYPixel(-6));

        c.lineTo(graph.width, getYPixel(-6));

        c.moveTo(xPadding, getYPixel(-9));

        c.lineTo(graph.width, getYPixel(-9));

        c.moveTo(xPadding, getYPixel(-12));

        c.lineTo(graph.width, getYPixel(-12));

        c.moveTo(xPadding, getYPixel(10));

        c.lineTo(graph.width, getYPixel(10));

        c.moveTo(xPadding, getYPixel(20));

        c.lineTo(graph.width, getYPixel(20));

        c.moveTo(xPadding, getYPixel(30));

        c.lineTo(graph.width, getYPixel(30));

        c.moveTo(xPadding, getYPixel(40));

        c.lineTo(graph.width, getYPixel(40));

        c.moveTo(xPadding, getYPixel(50));

        c.lineTo(graph.width, getYPixel(50));

        c.moveTo(xPadding, getYPixel(60));

        c.lineTo(graph.width, getYPixel(60));

        c.stroke();



        // Draw the X value texts

       /* var myMaxX = getMaxX();

        for(var i = d; i <= myMaxX; i +=zoom*100) {

            // uses data.values[i].X

            c.fillText(i, getXPixel(i), graph.height - yPadding + 20);

        }*/

        if(env.values[env.values.length-1].X>d){

         i=0;

        while(env.values[i].X<=d && i < env.values.length) i++;

        if(i!=0)d1=i-1;else d1=0;



        i=0;

        while(env.values[i].X<f && i < env.values.length-1) i++;

        f1=i ;



        pas=Math.floor((f1-d1)/10)+1;

        c.fillStyle="#000";

        c.textAlign = "right"

        c.textBaseline = "middle";

        //

        // les stations

        //

        var k=d1;

        while(k>0 && env.values[k].sta!=1)k--;

        if(k!=0){

            c.save();



          c.translate(xPadding, graph.height - yPadding + 100);

          c.rotate(-60 * Math.PI / 180);



            c.fillText(env.values[k].nom,0,0);



          c.rotate(60 * Math.PI / 180);

           c.restore();

         }

         //

        for(var i = d1; i < f1; i++) if(env.values[i].sta==1 || (i-d1)%pas==0){

          c.save();



          c.translate(getXPixel(env.values[i].X), graph.height - yPadding + 100);

          c.rotate(-60 * Math.PI / 180);



            c.fillText(env.values[i].nom,0,0);



          c.rotate(60 * Math.PI / 180);

           c.restore();

        }

        //

         var k=f1;

         if(k<0)k=0;

        while(k<env.values.length && env.values[k].sta!=1)k++;

        if(k<env.values.length){

            c.save();



          c.translate(graph.width, graph.height - yPadding + 100);

          c.rotate(-60 * Math.PI / 180);



            c.fillText(env.values[k].nom,0,0);



          c.rotate(60 * Math.PI / 180);

           c.restore();

         }

       }



        //

        //



        c.fillStyle="#000";

        // Draw the Y value texts

        c.textAlign = "right"

        c.textBaseline = "middle";

         c.fillText('Mode', xPadding - 10, getYPixel(0));

         c.fillText('Gong', xPadding - 10, getYPixel(-3));

         c.fillText('Klaxon', xPadding - 10, getYPixel(-6));

         c.fillText('FU', xPadding - 10, getYPixel(-9));

        // c.fillText('patin', xPadding - 10, getYPixel(-12));

        for(var i = 10; i < getMaxY(); i+=10) {

            c.fillText(i, xPadding - 10, getYPixel(i));

        }

        c.stroke();

        //c.strokeStyle = '#f0f';



        // Draw the line graph

        let data1={values:[]};

        data1.values=data.values.filter(f=>f.Y==0 || (f.X % zoom)==0).filter(f=>f.X>=d);



        for(var i = 0; i < data1.values.length-1; i +=1) if(data1.values[i+1]){

          c.beginPath();

          c.strokeStyle = data1.values[i].color;

          c.moveTo(getXPixel(data1.values[i].X), getYPixel(data1.values[i].Y));



          c.lineTo(getXPixel(data1.values[i+1].X), getYPixel(data1.values[i+1].Y));

          c.stroke();



        }



        // enveloppe

        if(env.values[env.values.length-1].X>d){

        i=0;

        while(env.values[i].X<=d && i < env.values.length) i++;

        if(i!=0)d1=i-1;else d1=0;



        i=0;

        while(env.values[i].X<f && i < env.values.length-1) i++;

        f1=i;

        c.beginPath();

        c.strokeStyle = "#2dc4ea";

        c.moveTo(xPadding, getYPixel(env.values[d1].Y));

      //  if (d1>=f1) {d1--;}

        //console.log(f1);

        for(var i = d1+1; i < f1+1; i++) {

         //   console.log(i);

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i-1].Y));

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i].Y));

        }

        c.lineTo(getXPixel(Math.floor(f)), getYPixel(env.values[env.values.length-1].Y));

        c.stroke();

      }

        // gong

c.fillStyle = '#fa0';

for(var i = d; i < f-1; i +=1)if(data.values[i].gong==1) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(-3), 4, 0, Math.PI * 1, true);

    c.fill();



}

//FU

c.fillStyle = '#000';

for(var i = d; i < f-1; i +=1)if(data.values[i].FU==1) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(-9), 4, 0, Math.PI * 1, true);

    c.fill();



}

//klaxon

c.fillStyle = '#f50';

for(var i = d; i < f-1; i++)if(data.values[i].klaxon==1) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(-6), 4, 0, Math.PI * 1, true);

    c.fill();



}

//patin

c.fillStyle = '#00f';

for(var i = d; i < f-1; i +=1)if(data.values[i].patin==1) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(-12), 4, 0, Math.PI * 1, true);

    c.fill();



}

 //frein

 c.fillStyle = '#f00';

for(var i = d; i < f-zoom; i +=zoom)if(data.values[i].traction==1) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(0), 4, 0, Math.PI * 1, true);

    c.fill();



}

//traction

c.fillStyle = '#0f0';

for(var i = d; i < f-zoom; i +=zoom)if(data.values[i].traction==2) {

    c.beginPath();

    c.arc(getXPixel(data.values[i].X), getYPixel(0), 4, 0, Math.PI * 1, true);

    c.fill();



}



}; // end $(function(){});





function leminigraph(data,env,d,f){

    //alert("apres "+d+" "+f);

  //  alert('test graph');

  if(d<0)d=0;

  if (f>data.values.length) f=data.values.length;

    zoom=30;



    var graph = document.getElementById("minigraph");

    var ctx = graph.getContext("2d");







 //   var graph;

    var xPadding = 50;

    var yPadding = 0;

    ctx.clearRect(0, 0, graph.width, graph.height);

    // Notice I changed The X values















    // show tooltip when mouse hovers over dot



    // Returns the max Y value in our data list

    function getMaxY() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].Y > max) {

                max = data.values[i].Y;

            }

        }



        max += 10 - max % 10;

        max=65;

        return max;

    }



    // Returns the max X value in our data list

    function getMaxX() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].X > max) {

                max = data.values[i].X;

            }

        }



        // omited

      //max += 10 - max % 10;

        return max;

    }







    // Return the x pixel for a graph point

    function getXPixel(val) {

        // uses the getMaxX() function

         return ((graph.width - xPadding)/ (getMaxX()-data.values[d].X))*(val-data.values[d].X)+xPadding;

        // was

      //  return (val*(graph.width-xpadding)/getMaxX())+xPadding;

      //return ((graph.width - xPadding) / getMaxX()) * val + (xPadding * 1.5);

    }



    // Return the y pixel for a graph point

    function getYPixel(val) {

        return graph.height - (((graph.height - yPadding) / getMaxY()) * val) - yPadding;

    }









        graph = document.getElementById("minigraph");

        var c = graph.getContext('2d');

         // Draw the axises

        c.beginPath();

        c.strokeStyle="#ddd";

        c.moveTo(xPadding, getYPixel(0));

        c.lineTo(graph.width, getYPixel(0));

        c.moveTo(xPadding, getYPixel(10));

        c.lineTo(graph.width, getYPixel(10));

        c.moveTo(xPadding, getYPixel(20));

        c.lineTo(graph.width, getYPixel(20));

        c.moveTo(xPadding, getYPixel(30));

        c.lineTo(graph.width, getYPixel(30));

        c.moveTo(xPadding, getYPixel(40));

        c.lineTo(graph.width, getYPixel(40));

        c.moveTo(xPadding, getYPixel(50));

        c.lineTo(graph.width, getYPixel(50));

        c.moveTo(xPadding, getYPixel(60));

        c.lineTo(graph.width, getYPixel(60));

        c.stroke();



        //--------



        //





        //

        //



        c.fillStyle="#000";

        // Draw the Y value texts

        c.textAlign = "right"

        c.textBaseline = "middle";



        for(var i = 0; i < getMaxY(); i+=10) {

            c.fillText(i, xPadding - 10, getYPixel(i));

        }

        c.stroke();







        //c.strokeStyle = '#f0f';



        // Draw the line graph



        let data1={values:[]};

        data1.values=data.values.filter(f=>f.Y==0 || (f.X % zoom)==0).filter(f=>f.X>=d);



        for(var i = 0; i < data1.values.length-1; i +=1) if(data1.values[i+1]){

          c.beginPath();

          c.strokeStyle = data1.values[i].color;

          c.moveTo(getXPixel(data1.values[i].X), getYPixel(data1.values[i].Y));



          c.lineTo(getXPixel(data1.values[i+1].X), getYPixel(data1.values[i+1].Y));

          c.stroke();



        }

        /* window.requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;

        i=d;

        function repeter(){



            c.beginPath();

            c.strokeStyle = data.values[i].color;

            c.moveTo(getXPixel(data.values[i].X), getYPixel(data.values[i].Y));



            c.lineTo(getXPixel(data.values[i+zoom].X), getYPixel(data.values[i+zoom].Y));

            c.stroke();



          //  $("#progress").progressbar( "option", "value", (i*100)/f );

          //  console.log(i);

        i +=zoom;

        if(i < f-zoom)window.requestAnimationFrame(repeter);

      }

      window.requestAnimationFrame(repeter);

       */



        // enveloppe

        i=0;

        while(env.values[i].X<=d && i < env.values.length) i++;

        if(i!=0)d1=i-1;else d1=0;



        i=0;

        while(env.values[i].X<f && i < env.values.length-1) i++;

        f1=i;

        c.beginPath();

        c.strokeStyle = "#2dc4ea";

        c.moveTo(xPadding, getYPixel(env.values[d1].Y));

        for(var i = d1+1; i < f1+1; i ++) {

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i-1].Y));

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i].Y));

        }

        c.lineTo(getXPixel(Math.floor(f)), getYPixel(env.values[env.values.length-1].Y));

        c.stroke();









}; // end $(function(){});







function leminigraphe(data,env,d,f){

    //alert("apres "+d+" "+f);

  //  alert('test graph');

  if(d<0)d=0;

  if (f>=data.values.length) f=data.values.length;

    zoom=30;



    var graph = document.getElementById("minigraphe");

    var ctx = graph.getContext("2d");







 //   var graph;

    var xPadding = 50;

    var yPadding = 80;

    ctx.clearRect(0, 0, graph.width, graph.height);

    // Notice I changed The X values















    // show tooltip when mouse hovers over dot



    // Returns the max Y value in our data list

    function getMaxY() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].Y > max) {

                max = data.values[i].Y;

            }

        }



        max += 10 - max % 10;

        max=65;

        return max;

    }



    // Returns the max X value in our data list

    function getMaxX() {

        var max = 0;



        for(var i = d; i < f; i ++) {

            if(data.values[i].X > max) {

                max = data.values[i].X;

            }

        }



        // omited

      //max += 10 - max % 10;

        return max;

    }







    // Return the x pixel for a graph point

    function getXPixel(val) {

        // uses the getMaxX() function

         return ((graph.width - xPadding-10)/ (getMaxX()-data.values[d].X))*(val-data.values[d].X)+xPadding;

        // was

      //return ((graph.width - xPadding) / getMaxX()) * val + (xPadding * 1.5);

    }



    // Return the y pixel for a graph point

    function getYPixel(val) {

        return graph.height - (((graph.height - yPadding) / getMaxY()) * val) - yPadding;

    }









        graph = document.getElementById("minigraphe");

        var c = graph.getContext('2d');

         // Draw the axises

        c.beginPath();

        c.moveTo(xPadding, 0);

        c.lineTo(xPadding, graph.height- yPadding );

        c.lineTo(graph.width, graph.height - yPadding);

        c.stroke();



        c.beginPath();

        c.strokeStyle="#ddd";

        c.moveTo(xPadding, getYPixel(10));

        c.lineTo(graph.width, getYPixel(10));

        c.moveTo(xPadding, getYPixel(20));

        c.lineTo(graph.width, getYPixel(20));

        c.moveTo(xPadding, getYPixel(30));

        c.lineTo(graph.width, getYPixel(30));

        c.moveTo(xPadding, getYPixel(40));

        c.lineTo(graph.width, getYPixel(40));

        c.moveTo(xPadding, getYPixel(50));

        c.lineTo(graph.width, getYPixel(50));

        c.moveTo(xPadding, getYPixel(60));

        c.lineTo(graph.width, getYPixel(60));

        c.stroke();

        //--------

         i=0;

        while(env.values[i].X<=d && i < env.values.length) i++;

        if(i!=0)d1=i-1;else d1=0;



        i=0;

        while(env.values[i].X<f && i < env.values.length-1) i++;

        f1=i ;



        pas=Math.floor((f1-d1)/10)+1;

        c.fillStyle="#000";

        c.textAlign = "right"

        c.textBaseline = "middle";

        //

        // les stations

        //



         //

        for(var i = d1; i < f1; i++) if(env.values[i].sta==1){

          c.save();



          c.translate(getXPixel(env.values[i].X), graph.height - yPadding +5);

          c.rotate(-60 * Math.PI / 180);



            c.fillText(env.values[i].nom,0,0);



          c.rotate(60 * Math.PI / 180);

           c.restore();

        }

        //





        //

        //



        c.fillStyle="#000";

        // Draw the Y value texts

        c.textAlign = "right"

        c.textBaseline = "middle";



        for(var i = 0; i < getMaxY(); i+=10) {

            c.fillText(i, xPadding - 10, getYPixel(i));

        }

        c.stroke();





        for(var i = 0; i < getMaxY(); i+=10) {

            c.fillText(i, xPadding - 10, getYPixel(i));

        }

        c.stroke();

        //c.strokeStyle = '#f0f';



        // Draw the line graph



        let data1={values:[]};

        data1.values=data.values.filter(f=>f.Y==0 || (f.X % zoom)==0).filter(f=>f.X>=d);



        for(var i = 0; i < data1.values.length-1; i +=1) if(data1.values[i+1]){

          c.beginPath();

          c.strokeStyle = data1.values[i].color;

          c.moveTo(getXPixel(data1.values[i].X), getYPixel(data1.values[i].Y));



          c.lineTo(getXPixel(data1.values[i+1].X), getYPixel(data1.values[i+1].Y));

          c.stroke();



        }



        // enveloppe

        i=0;

        while(env.values[i].X<=d && i < env.values.length) i++;

        if(i!=0)d1=i-1;else d1=0;



        i=0;

        while(env.values[i].X<f && i < env.values.length-1) i++;

        f1=i;

        c.beginPath();

        c.strokeStyle = "#2dc4ea";

        c.moveTo(xPadding, getYPixel(env.values[d1].Y));

        for(var i = d1+1; i < f1+1; i ++) {

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i-1].Y));

            c.lineTo(getXPixel(Math.floor(env.values[i].X)), getYPixel(env.values[i].Y));

        }

        c.lineTo(getXPixel(Math.floor(f)), getYPixel(env.values[env.values.length-1].Y));

        c.stroke();









} // end $(function(){});



