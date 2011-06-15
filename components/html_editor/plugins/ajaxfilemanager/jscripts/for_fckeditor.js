//function below added by logan (cailongqun [at] yahoo [dot] com [dot] cn) from www.phpletter.com
function selectFile(url)
{
  if(url != '')
  {
      //window.opener.SetUrl(url) ;
      var CKEditorFuncNum;
      var regexS = "[\\?&]CKEditorFuncNum=([^&#]*)";
      var regex = new RegExp( regexS );
      var results = regex.exec( window.location.href );
      if( results == null )
          CKEditorFuncNum="";
      else
          CKEditorFuncNum=results[1];
      window.top.opener['CKEDITOR'].tools.callFunction(CKEditorFuncNum, url);
      window.close() ;
  }else
  {
     alert(noFileSelected);
  }
  

}



function cancelSelectFile()
{
  // close popup window
  window.close() ;
}