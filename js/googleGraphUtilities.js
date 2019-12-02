/**
 * @brief Set of functions that convert google flash-based graphs to images
 *
 * @file googleGraphUtilties.js
 * $Revision: 200 $
 * $Date:: 2012-10-16 10:55:45 #$: Date of last commit
 * $URL: https://srcvault.icts.uiowa.edu/repos/REDCap/REDCap/trunk/geomarker/js/googleGraphUtilities.js $
 *
 * @author Riccardo Govoni
 * @see http://www.battlehorse.net/page/topics/charts/save_google_charts_as_image.html
 */


/**
 * @brief Used by functions saveAsImg and toImage
 *
 * @param  charContainer Name of the <div> containing the graph
 * @retval imgData      The PNG image file data
 */
function getImgData(chartContainer) {
   var svg = $(chartContainer).find('svg').parent().html();
   var doc = chartContainer.ownerDocument;
   var canvas = doc.createElement('canvas');

   canvas.setAttribute('style', 'position: absolute; ' + '');
   doc.body.appendChild(canvas);
   canvg(canvas, svg);
   var imgData = canvas.toDataURL("image/png");
   canvas.parentNode.removeChild(canvas);
   return imgData;
}


/**
 * @brief Saves a google graph as a PNG download
 *
 * @param  charContainer Name of the <div> containing the graph
 */
function saveAsImg(chartContainer) {
  var imgData = getImgData(chartContainer);

  // Replacing the mime-type will force the browser to trigger a download
  // rather than displaying the image in the browser window.
  window.location = imgData.replace("image/png", "image/octet-stream");
}


/**
 * @brief Display google graph as an image inside a <div>
 *
 * @param  charContainer Name of the <div> containing the original graph
 * @param  imgContainer  Name of the <div> containing the destination graph
 */
function toImg(chartContainer, imgContainer) {
  var doc = chartContainer.ownerDocument;
  var img = doc.createElement('img');
  img.src = getImgData(chartContainer);

  while (imgContainer.firstChild) {
    imgContainer.removeChild(imgContainer.firstChild);
  }
  imgContainer.appendChild(img);
}
