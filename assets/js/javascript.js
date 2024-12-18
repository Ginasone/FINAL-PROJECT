const video = document.getElementById('myVideo');
const container = document.querySelector('.vid-container');
const first = document.getElementById('first');
const second = document.getElementById('second');
let firstClick = true;

container.addEventListener('click', function(){
    if(firstClick){
        video.play();
        video.muted=false;
        first.style.display='none';
        second.style.display='block';
        firstClick=false;
    }
    else{
        window.location.href='/FINAL PROJECT/view/homepage.php'
    }
    
});

video.addEventListener('ended', function(){
    video.play();
})