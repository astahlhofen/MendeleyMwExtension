var closeTime = setInterval( function() { closeWindow(); } ,1500 );
var windowHandle;

function openInMendeley( link ) 
{
	windowHandle = window.open( '','name','height=250,width=700' );
	windowHandle.location = link;
}

function closeWindow( ) 
{
	window.windowHandle.close();
}