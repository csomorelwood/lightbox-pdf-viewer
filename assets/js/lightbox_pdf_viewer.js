let currentPage = 1;
let pdfjsLib, loadingTask, numOfPages, scale = 2, canvasScale = 1
const wrapperID = 'lightbox-pdf-viewer-wrapper'
const canvaswrapID = 'lightbox-pdf-viewer-canvas-wrap'
const canvasID = 'lightbox-pdf-viewer-by-csomor'
const prevBtnID = 'lightbox-pdf-viewer-prev'
const nextBtnID = 'lightbox-pdf-viewer-next'
const navbarID = 'lightbox-pdf-viewer-navbar'
const counterID = 'lightbox-pdf-viewer-counter'
const closeID = 'lightbox-pdf-viewer-close'
const newtabID = 'lightbox-pdf-viewer-newtab'
const audioID = 'lightbox-pdf-viewer-audio'
const audiosrcID = 'lightbox-pdf-viewer-audiosource'
const zoominID = 'lightbox-pdf-viewer-zoomin'
const zoomoutID = 'lightbox-pdf-viewer-zoomout'

function openLightBoxPDFView(pdf_url, mp3_url){
  if(window.location.protocol === "https:"){
    pdf_url.replace("http:", "https:");
  }
  else{
    pdf_url.replace("https:", "http:");
  }
  pdfjsLib = window['pdfjs-dist/build/pdf'];
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.7.570/pdf.worker.min.js';
  document.getElementById(wrapperID) ? document.getElementById(wrapperID).classList.remove('hidden') : console.log('no lightbox pdf wrapper found')
  document.getElementById('download-pdf').href = pdf_url
  document.getElementById('pdf-new-tab').href = pdf_url
  document.getElementById('newtab-img').src = window.location.protocol + '//' + window.location.hostname + '/wp-content/plugins/pdf_viewer_by_csomor/assets/images/expand.png'
  document.getElementById(zoominID).style.backgroundImage = 'url(' + window.location.protocol + '//' + window.location.hostname + '/wp-content/plugins/pdf_viewer_by_csomor/assets/images/zoom-in.png)'
  document.getElementById(zoomoutID).style.backgroundImage = 'url(' + window.location.protocol + '//' + window.location.hostname + '/wp-content/plugins/pdf_viewer_by_csomor/assets/images/zoom-out.png)'
  currentPage = 1
  document.body.classList.add('no-scroll')

  insertMP3(mp3_url);

  // Asynchronous download of PDF
  loadingTask = pdfjsLib.getDocument(pdf_url);
  render();
}

document.addEventListener('DOMContentLoaded', ()=>{
  let pdfWrapper = document.createElement('div')
  pdfWrapper.id = wrapperID
  pdfWrapper.classList.add('hidden')
  
  let canvaswrap = document.createElement('div')
  canvaswrap.id = canvaswrapID
  canvaswrap.width = 500
  canvaswrap.height = 300

  let canvas = document.createElement('canvas')
  canvas.id = canvasID
  canvas.width = 500
  canvas.height = 300
  canvaswrap.appendChild(canvas)

  let prevBtn = document.createElement('button')
  prevBtn.id = prevBtnID
  
  let nextBtn = document.createElement('button')
  nextBtn.id = nextBtnID

  let navbar = document.createElement('div')
  navbar.id = navbarID

  let counter = document.createElement('div')
  counter.id = counterID
  counter.innerHTML = '<div class="pagecount"><span id="page_num"></span> / <span id="page_count"></span></div><a id="download-pdf" href="#" download>⭳</a><a id="pdf-new-tab" href="#" target="_blank"><img id="newtab-img" /></a>'
  
  let zoomin = document.createElement('div')
  zoomin.id = zoominID
  zoomin.addEventListener('click', ()=>{
    if(canvasScale <= 3){
      canvasScale += 0.25
    }
    document.getElementById(canvasID).style.transform = 'scale(' + canvasScale + ')'
  })
  counter.appendChild(zoomin)
  
  let zoomout = document.createElement('div')
  zoomout.id = zoomoutID
  zoomout.addEventListener('click', ()=>{
    if(canvasScale > 1){
      canvasScale -= 0.25
    }
    document.getElementById(canvasID).style.transform = 'scale(' + canvasScale + ')'
  })
  counter.appendChild(zoomout)

  navbar.appendChild(counter)
  
  let close = document.createElement('div')
  close.id = closeID
  close.addEventListener('click', ()=>{
    document.getElementById(wrapperID) ? document.getElementById(wrapperID).classList.add('hidden') : console.log('no lightbox pdf wrapper found')
    document.body.classList.remove('no-scroll')
  })
  
  
  let audiosrc = document.createElement('source')
  audiosrc.id = audiosrcID
  audiosrc.type = 'audio/mpeg'

  let audio = document.createElement('audio')
  audio.id = audioID
  audio.controls = true
  audio.preload = 'none'
  audio.appendChild(audiosrc)
  navbar.appendChild(audio)

  pdfWrapper.appendChild(prevBtn)
  pdfWrapper.appendChild(canvaswrap)
  pdfWrapper.appendChild(nextBtn)
  pdfWrapper.appendChild(navbar)
  pdfWrapper.appendChild(close)

  document.body.appendChild(pdfWrapper)

  document.getElementById(nextBtnID).addEventListener('click', (e)=>{
    if(currentPage<numOfPages){
      currentPage++;
    }
    render();
  })
  document.getElementById(prevBtnID).addEventListener('click', (e)=>{
    if(currentPage>1){
      currentPage--;
    }
    render();
  })
})

function insertMP3(mp3_url){
  if(mp3_url != ''){
    let source = document.getElementById(audiosrcID)
    let player = document.getElementById(audioID)
    if(player.ended || source.src != mp3_url){
      source.src = mp3_url
      player.load()
      player.play()
    }
  }
}

function render(){
  loadingTask.promise.then(function(pdf) {
    console.log('PDF loaded');
    
    // Fetch the first page
    pdf.getPage(currentPage).then(function(page) {
      console.log('Page loaded');
      numOfPages = pdf.numPages
      let viewport = page.getViewport({scale: scale});
  
      let canvas = document.getElementById(canvasID);
      let context = canvas.getContext('2d');
      //canvas.height = viewport.height - 200;
      //canvas.width = viewport.width;
      canvas.height = page.view[3] * scale
      canvas.width = page.view[2] * scale

      let canvaswrap = document.getElementById(canvaswrapID);
      canvaswrap.height = page.view[3] * scale
      canvaswrap.width = page.view[2] * scale

      let renderContext = {
        canvasContext: context,
        viewport: viewport
      };
      let renderTask = page.render(renderContext);
      renderTask.promise.then(function () {
        console.log('Page rendered');
      });
      document.getElementById('page_num').innerText = currentPage
      document.getElementById('page_count').innerText = numOfPages
    });
  }, function (reason) {
    // PDF loading error
    console.error(reason);
  });
}
