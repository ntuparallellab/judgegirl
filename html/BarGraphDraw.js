
/*

        Author:		Robbe D. Morris
		Date:		September 29, 2002
		URL:			http://www.robbemorris.com

*/

 var BarGraphBarColor = '#330066';
 var BarGraphBarBorder = '2';
 var BarGraphMaxWidth=200; 
 var BarGraphMathOption=1;
 var BarGraphTableWidth=430;
 var BarGraphTableTDWidth=230;
 var BarGraphMathTypePercent=1;
 var BarGraphMathTypeScore=2;
 var BarGraphMathTypeVote=3;


 function BarGraphDrawTable(GraphArrays)
  {

      
      var sBarGraphName='';
      var sBarGraphImgDiv='';
      var sBarGraphLock='';
      var sBarGraphWgt='';
      var sH='';
      var Idx=0;
      var lArrayLength=0;
      var lLoop=0;
      var lFoundCnt=0;
      var oBarGraphRow;
	  var nWidth=0;
	  var nWeight=0;
	  var sBarGraphBarBorder='BORDER-RIGHT: ' + BarGraphBarBorder + 'px outset ' + BarGraphBarColor + '; BORDER-LEFT: ' + BarGraphBarBorder + 'px outset ' + BarGraphBarColor + '; BORDER-TOP: ' + BarGraphBarBorder + 'px outset ' + BarGraphBarColor + '; BORDER-BOTTOM: ' + BarGraphBarBorder + 'px outset ' + BarGraphBarColor;
      var sBarGraphWeightStyle='font-family:verdana,arial,helvetica;sans-serif color:#000000; font-size:12px;align=right; BORDER-RIGHT: 0px outset #5D7BBA; BORDER-LEFT: 0px outset #5D7BBA; BORDER-TOP: 0px outset #5D7BBA; BORDER-BOTTOM: 0px outset #5D7BBA; text-align: RIGHT;BACKGROUND-COLOR:#FFFFFF;height:20px;';
      var sBarGraphNameStyle='font-family:verdana,arial,helvetica;sans-serif color:#000000; font-size:12px; text-decoration:none; font-style:bold; background-color:#FFFFFF; ';
	  var sBarGraphInsStyle='font-family:verdana,arial,helvetica;sans-serif color:#000000; font-size:10px; text-decoration:none; font-style:bold; background-color:#FFFFFF; ';
	  var oBarGraph = document.getElementById('BarGraphTable');
	  var oBarGraphDIV;
 
   try
	  {

 
        lArrayLength = GraphArrays.length;

    	if (lArrayLength < 1) { return false; }
 
        sH += '<table border=0 align=left width=' + BarGraphTableWidth + ' cellpadding=0 cellspacing=2 >';
 
 
        for (lLoop=0; lLoop<lArrayLength; lLoop++)
       {
		  
           oBarGraphRow = GraphArrays[lLoop];
           Idx = lLoop;
  
           if (oBarGraphRow[idxBarGraphDisplay] == true)
            {
       
               lFoundCnt +=1;

               sBarGraphName = 'id=divBarGraph_' + Idx + ' name=divBarGraph_' + Idx;
               sBarGraphWgt = 'id=txtBarGraphWeight_' + Idx + ' name=txtBarGraphWeight_' + Idx;
           
   
              sH += '<tr>';
              sH += '<td align=left valign=middle nowrap width=125 style="' + sBarGraphNameStyle + '">' + oBarGraphRow[idxBarGraphDisplayName] + '&nbsp;</td>';
              sH += '<td align=left valign=top  width=' + BarGraphTableTDWidth + ' height=20 id=tblBarGraph1 name=tblBarGraph1 nowrap>';
              sH += '<div ' + sBarGraphName + ' style="' + sBarGraphBarBorder + ';position:relative;left:0px;top:0px;height:20;width:0;valign:top;BACKGROUND-COLOR:' + BarGraphBarColor + ';"></div></td>';
              sH += '<td align=right><input ' + sBarGraphWgt + ' type=text value="0.00" READONLY size=6 maxlength=30 style="' + sBarGraphWeightStyle + '" ></td>';
              sH += '</tr>';
 
              BarGraphMathOption = oBarGraphRow[idxBarGraphMathType];

            }
          }

             sH += '<tr><td align=center colspan=2>&nbsp;</td><td align=center style="' + sBarGraphInsStyle + '">';
	         sH += '</td><td>&nbsp;</td></tr>';
             sH += '</table>';
      
             oBarGraph.innerHTML=sH;

	         for (lLoop=0; lLoop<lArrayLength; lLoop++)
            {
		  
               oBarGraphRow = BarGraphArrays[lLoop];
               Idx = lLoop;
           
               if (oBarGraphRow[idxBarGraphDisplay] == true)
               {
       
                  lFoundCnt +=1;
                  oBarGraphDIV = document.getElementById('divBarGraph_' + Idx);

			      nWidth = BarGraphConvertWeightToWidth(oBarGraphRow[idxBarGraphDisplayWeight]); 
			               
			      if ((nWidth > BarGraphMaxWidth) || (nWidth < 0)) 
					  { 
					     nWidth = 0; 
					     nWeight = BarGraphConvertWidthToWeight(nWidth);
					   }
                  else { nWeight = oBarGraphRow[idxBarGraphDisplayWeight]; }
                
          
			      oBarGraphDIV.style.width=nWidth;
			  
			      document.getElementById("txtBarGraphWeight_" + Idx).value = BarGraphRoundNumber(nWeight,"0");
              
                 }
	        }
             BarGraphSaveElements(GraphArrays);
	      }
	       catch (exception) 
		  { 
		     if (exception.description == null) { alert("BarGraph Draw Error: " + exception.message); }  
		     else {  alert("BarGraph Draw Error: " + exception.description); }
		  }
  }


   function BarGraphWriteInputs(GraphArrays)
  {

      var sBarGraphName='';
      var lArrayLength=0;
      var lLoop=0;
	  var sH='';
      var oBarGraphRow;
 
     try
	  {

      var oBarGraph = document.getElementById('BarGraphHiddenElements');
  
        lArrayLength = GraphArrays.length;

    	if (lArrayLength < 1) { return false; }
 
        for (lLoop=0; lLoop<lArrayLength; lLoop++)
       {
		  
           oBarGraphRow = GraphArrays[lLoop]; 
           
		   sBarGraphName = 'id=savBarGraph_' + oBarGraphRow[idxBarGraphDisplayKey]  + ' name=savBarGraph_' + oBarGraphRow[idxBarGraphDisplayKey] ;  

		   sH += '<input ' + sBarGraphName + ' type=hidden value="' + oBarGraphRow[idxBarGraphDisplayWeight] + '" size=10 maxlength=30 size=10>'; 
	 
        }
         
        oBarGraph.innerHTML=sH;
	 
  } 
    catch (exception) 
		  { 
		     if (exception.description == null) { alert("BarGraph Hidden Elements: " + exception.message); }  
		     else {  alert("BarGraph Hidden Elements: " + exception.description); }
		  }
  }



 function BarGraphSaveElements(GraphArrays)
   {

      var lArrayLength=0;
      var lLoop=0;
      var oBarGraphRow;
	  var sKey;
 
   
      try
	  {

        lArrayLength = GraphArrays.length;

    	if (lArrayLength < 1) { return false; }
 
        for (lLoop=0; lLoop<lArrayLength; lLoop++)
       {
		  
           oBarGraphRow = GraphArrays[lLoop];
    
           if (oBarGraphRow[idxBarGraphDisplay] == true)
            {
               oBarGraphRow[idxBarGraphDisplayWeight] = document.getElementById("txtBarGraphWeight_" + lLoop).value;
			   document.getElementById("savBarGraph_" + oBarGraphRow[idxBarGraphDisplayKey]).value = oBarGraphRow[idxBarGraphDisplayWeight];
			}
        }
	  }
	  catch (e) {}
   }


  

	function BarGraphConvertWidthToWeight(nWidth)
	{
         var nRet=0;
		 var nMax=0;
		 var nWeight=0;

		 nMax = parseFloat(BarGraphMaxWidth);
         nWidth = parseFloat(nWidth);
 
         switch (BarGraphMathOption)
		{

              case 1:
 
                           nWeight = (nWidth / nMax) * 100;	   
                           nRet =  nWeight;
				           break;

			  case 2:
 
				           nWeight = (nWidth / nMax) * 9;	
						   if (nWeight <1) { nWeight=1; }
                           nRet =  nWeight;
				           break;

			  case 3:
 
				           nWeight = (nWidth / nMax) * BarGraphVoteMax;	
                           nRet =  nWeight;
				           break;

		}

		 return nRet;
		  
	}

	function BarGraphConvertWeightToWidth(nWeight)
	{
         var nRet=0;
		 var nMax=0;
		 var nWidthPercent=0;
		  
		 nMax = parseFloat(BarGraphMaxWidth);
         nWeight = parseFloat(nWeight);
 
         switch (BarGraphMathOption)
		{

              case 1:

                           nWeight = nWeight * 2;						   
                           nRet = BarGraphRoundNumber(nWeight,"1");
				           break;

			  case 2:
				           if ((nWeight<1) || (nWeight >9)) { nWeight=1;}
						   nWidthPercent = nWeight / 9;
                           nRet = BarGraphRoundNumber(nMax * nWidthPercent,"1");
				           break;

			  case 3:

						   nWidthPercent = nWeight / BarGraphVoteMax;
                           nRet = BarGraphRoundNumber(nMax * nWidthPercent,"1");
				           break;

		}

		 return nRet;
		  
	}

	  function BarGraphRoundNumber(number,X)
  {
	  
		var number2;
		var TmpNum;

		 X=(!X ? 1:X);
	
		number2 = Math.round(number*Math.pow(10,X))/Math.pow(10,X);
		TmpNum = "" + number2;
		var TmpArray = TmpNum.split(".");
		if (TmpArray.length <2 && X != 0 ) { number2 = number2 + ".0"; }
	 
	    return number2;
  }
 
