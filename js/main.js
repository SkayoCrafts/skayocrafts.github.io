
function ready() {
	var links = document.getElementsByTagName('a');
	var animated = document.getElementsByClassName('animated');

	for (var i = 0; i < links.length; i++) {
		links[i].onclick = function (event) {
			if (event.currentTarget.target != '_blank') {
				event.preventDefault();

				for (var j = 0; j < animated.length; j++) {
					animated[j].classList.add(animated[j].dataset.reverse);
				}

				var link = event.currentTarget.href;

				setTimeout(function () {
					window.location.href = link;
				}, 1000)
			}
		};
	}
}

if (document.readyState != 'loading'){
	ready();
} else {
	document.addEventListener('DOMContentLoaded', ready);
}