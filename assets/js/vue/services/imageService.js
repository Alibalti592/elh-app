const imageService = {
    async getBase64ResizeFromFile(file, maxWidth = 400, maxHeight = 350, mimeType = false) {
        return new Promise((resolve) => {
            //ENCODE 64
            let fileReader = new FileReader();
            fileReader.addEventListener("load", async function (e) {
                let base64 = e.target.result;
                const result = await imageService.resizeImageFromBase64(base64, maxWidth, maxHeight, mimeType);
                resolve(result);
            });
            fileReader.readAsDataURL(file);
        });
    },
    getBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    },
    //en kb
    getImageSizeFromBase64(base64String) {
        const stringLength = base64String.length;
        return  Math.round((4 * Math.ceil(stringLength / 3) * 0.5624896334383812) / 1000);
    },
    resizeImageFromBase64(base64Str, maxWidth = 400, maxHeight = 350, mimeType = false) {
        function downScaleCanvas(cv, scale) {
            if (!(scale < 1) || !(scale > 0)) throw ('scale must be a positive number <1 ');
            var sqScale = scale * scale; // square scale = area of source pixel within target
            var sw = cv.width; // source image width
            var sh = cv.height; // source image height
            var tw = Math.floor(sw * scale); // target image width
            var th = Math.floor(sh * scale); // target image height
            var sx = 0, sy = 0, sIndex = 0; // source x,y, index within source array
            var tx = 0, ty = 0, yIndex = 0, tIndex = 0; // target x,y, x,y index within target array
            var tX = 0, tY = 0; // rounded tx, ty
            var w = 0, nw = 0, wx = 0, nwx = 0, wy = 0, nwy = 0; // weight / next weight x / y
            // weight is weight of current source point within target.
            // next weight is weight of current source point within next target's point.
            var crossX = false; // does scaled px cross its current px right border ?
            var crossY = false; // does scaled px cross its current px bottom border ?
            var sBuffer = cv.getContext('2d').
            getImageData(0, 0, sw, sh).data; // source buffer 8 bit rgba
            var tBuffer = new Float32Array(3 * tw * th); // target buffer Float32 rgb
            var sR = 0, sG = 0,  sB = 0; // source's current point r,g,b
            /* untested !
            var sA = 0;  //source alpha  */

            for (sy = 0; sy < sh; sy++) {
                ty = sy * scale; // y src position within target
                tY = 0 | ty;     // rounded : target pixel's y
                yIndex = 3 * tY * tw;  // line index within target array
                crossY = (tY != (0 | ty + scale));
                if (crossY) { // if pixel is crossing botton target pixel
                    wy = (tY + 1 - ty); // weight of point within target pixel
                    nwy = (ty + scale - tY - 1); // ... within y+1 target pixel
                }
                for (sx = 0; sx < sw; sx++, sIndex += 4) {
                    tx = sx * scale; // x src position within target
                    tX = 0 |  tx;    // rounded : target pixel's x
                    tIndex = yIndex + tX * 3; // target pixel index within target array
                    crossX = (tX != (0 | tx + scale));
                    if (crossX) { // if pixel is crossing target pixel's right
                        wx = (tX + 1 - tx); // weight of point within target pixel
                        nwx = (tx + scale - tX - 1); // ... within x+1 target pixel
                    }
                    sR = sBuffer[sIndex    ];   // retrieving r,g,b for curr src px.
                    sG = sBuffer[sIndex + 1];
                    sB = sBuffer[sIndex + 2];

                    /* !! untested : handling alpha !!
                       sA = sBuffer[sIndex + 3];
                       if (!sA) continue;
                       if (sA != 0xFF) {
                           sR = (sR * sA) >> 8;  // or use /256 instead ??
                           sG = (sG * sA) >> 8;
                           sB = (sB * sA) >> 8;
                       }
                    */
                    if (!crossX && !crossY) { // pixel does not cross
                        // just add components weighted by squared scale.
                        tBuffer[tIndex    ] += sR * sqScale;
                        tBuffer[tIndex + 1] += sG * sqScale;
                        tBuffer[tIndex + 2] += sB * sqScale;
                    } else if (crossX && !crossY) { // cross on X only
                        w = wx * scale;
                        // add weighted component for current px
                        tBuffer[tIndex    ] += sR * w;
                        tBuffer[tIndex + 1] += sG * w;
                        tBuffer[tIndex + 2] += sB * w;
                        // add weighted component for next (tX+1) px
                        nw = nwx * scale
                        tBuffer[tIndex + 3] += sR * nw;
                        tBuffer[tIndex + 4] += sG * nw;
                        tBuffer[tIndex + 5] += sB * nw;
                    } else if (crossY && !crossX) { // cross on Y only
                        w = wy * scale;
                        // add weighted component for current px
                        tBuffer[tIndex    ] += sR * w;
                        tBuffer[tIndex + 1] += sG * w;
                        tBuffer[tIndex + 2] += sB * w;
                        // add weighted component for next (tY+1) px
                        nw = nwy * scale
                        tBuffer[tIndex + 3 * tw    ] += sR * nw;
                        tBuffer[tIndex + 3 * tw + 1] += sG * nw;
                        tBuffer[tIndex + 3 * tw + 2] += sB * nw;
                    } else { // crosses both x and y : four target points involved
                        // add weighted component for current px
                        w = wx * wy;
                        tBuffer[tIndex    ] += sR * w;
                        tBuffer[tIndex + 1] += sG * w;
                        tBuffer[tIndex + 2] += sB * w;
                        // for tX + 1; tY px
                        nw = nwx * wy;
                        tBuffer[tIndex + 3] += sR * nw;
                        tBuffer[tIndex + 4] += sG * nw;
                        tBuffer[tIndex + 5] += sB * nw;
                        // for tX ; tY + 1 px
                        nw = wx * nwy;
                        tBuffer[tIndex + 3 * tw    ] += sR * nw;
                        tBuffer[tIndex + 3 * tw + 1] += sG * nw;
                        tBuffer[tIndex + 3 * tw + 2] += sB * nw;
                        // for tX + 1 ; tY +1 px
                        nw = nwx * nwy;
                        tBuffer[tIndex + 3 * tw + 3] += sR * nw;
                        tBuffer[tIndex + 3 * tw + 4] += sG * nw;
                        tBuffer[tIndex + 3 * tw + 5] += sB * nw;
                    }
                } // end for sx
            } // end for sy

            // create result canvas
            var resCV = document.createElement('canvas');
            resCV.width = tw;
            resCV.height = th;
            var resCtx = resCV.getContext('2d');
            var imgRes = resCtx.getImageData(0, 0, tw, th);
            var tByteBuffer = imgRes.data;
            // convert float32 array into a UInt8Clamped Array
            var pxIndex = 0; //
            for (sIndex = 0, tIndex = 0; pxIndex < tw * th; sIndex += 3, tIndex += 4, pxIndex++) {
                tByteBuffer[tIndex] = Math.ceil(tBuffer[sIndex]);
                tByteBuffer[tIndex + 1] = Math.ceil(tBuffer[sIndex + 1]);
                tByteBuffer[tIndex + 2] = Math.ceil(tBuffer[sIndex + 2]);
                tByteBuffer[tIndex + 3] = 255;
            }
            // writing result to canvas.
            resCtx.putImageData(imgRes, 0, 0);
            return resCV;
        }
        function downScaleImage(img, scale) {
            let imgCV = document.createElement('canvas');
            imgCV.width = img.width;
            imgCV.height = img.height;
            let imgCtx = imgCV.getContext('2d');
            imgCtx.drawImage(img, 0, 0);
            return downScaleCanvas(imgCV, scale);
        }

        return new Promise((resolve) => {
            let img = new Image();
            img.src = base64Str;
            img.onload = () => {
                // let scale;
                // let height = maxHeight;
                // let width = maxWidth;
                // if(img.height > img.width) {
                //     scale = Number((maxHeight / img.height).toFixed(3));
                // } else {
                //     scale = Number((maxWidth / img.height).toFixed(3));
                // }
                // let outputCanvas = downScaleImage(img, scale);
                // resolve(outputCanvas.toDataURL('image/jpeg', 1));
                    let height = maxHeight + 50;
                    let width = maxWidth + 50;
                    // Make sure the width and height preserve the original aspect ratio and adjust if needed
                    if(img.height < img.width) {
                        width = Math.floor(height * (img.width / img.height));
                    } else {
                        height = Math.floor(width * (img.height / img.width));
                    }

                    let resizingCanvas = document.createElement('canvas');
                    let resizingCanvasContext = resizingCanvas.getContext("2d");

                    // Start with original image size
                    resizingCanvas.width = img.width;
                    resizingCanvas.height = img.height;


                    // Draw the original image on the (temp) resizing canvas
                    resizingCanvasContext.drawImage(img, 0, 0, resizingCanvas.width, resizingCanvas.height);

                    let curImageDimensions = {
                        width: Math.floor(img.width),
                        height: Math.floor(img.height)
                    };

                    let halfImageDimensions = {
                        width: null,
                        height: null
                    };

                    // Quickly reduce the dize by 50% each time in few iterations until the size is less then
                    // 2x time the target size - the motivation for it, is to reduce the aliasing that would have been
                    // created with direct reduction of very big image to small image
                    while (curImageDimensions.width * 0.5 > width) {
                        // Reduce the resizing canvas by half and refresh the image
                        halfImageDimensions.width = Math.floor(curImageDimensions.width * 0.5);
                        halfImageDimensions.height = Math.floor(curImageDimensions.height * 0.5);

                        resizingCanvasContext.drawImage(resizingCanvas, 0, 0, curImageDimensions.width, curImageDimensions.height,
                            0, 0, halfImageDimensions.width, halfImageDimensions.height);

                        curImageDimensions.width = halfImageDimensions.width;
                        curImageDimensions.height = halfImageDimensions.height;
                    }

                    // Now do final resize for the resizingCanvas to meet the dimension requirments
                    // directly to the output canvas, that will output the final image
                    let outputCanvas = document.createElement('canvas');
                    let outputCanvasContext = outputCanvas.getContext("2d");

                    outputCanvas.width = width;
                    outputCanvas.height = height;

                    outputCanvasContext.drawImage(resizingCanvas, 0, 0, curImageDimensions.width, curImageDimensions.height,
                        0, 0, width, height);
                    //DEFINE TYPE ??? 'image/jpeg'
                    if(mimeType) {
                        resolve(outputCanvas.toDataURL(mimeType));
                    } else {
                        resolve(outputCanvas.toDataURL());
                    }
            }
        })
    },
}

export { imageService }