document.addEventListener("DOMContentLoaded",	function	()	{
    var event = document.getElementsByClassName('event');
    for(var i=0; i<event.length; i++) {
        event[i].addEventListener('click', function () {
            this.style.display = 'none';

        });
    }
});