<?php
class FileType
{
    public const IMAGE = array("jpg", "jpeg", "png");
    public const VIEW_PRINT = array("pdf"); 
}

class File
{
    public static int $allowedMaxSize = 16777216;

    public static function upload(array $file, array $fileType, String $into){
        $statusOfUpload = array();
        $errors = "";
        $newFileName = "";

        $nameArray = explode('.',$file['name']);
        $fileExt = strtolower(end($nameArray));
        if(count($fileType) !=0 && in_array($fileExt, $fileType)=== false){
            $errors .= "This extension isn't allowed.";
        }
        
        if($file['size'] > self::$allowedMaxSize) {
            $errors .='File size must be less than or equal to '.
            (self::$allowedMaxSize/(1024*1024)).' MB.';
        }
        
        if(empty($errors)==true) {
            $newFileName = "$into.$fileExt";
            move_uploaded_file($file['tmp_name'],$newFileName);
        }
        $statusOfUpload["errorUploadFile"] = $errors;
        $statusOfUpload["fileName"] = $newFileName;
        return $statusOfUpload;
    }
}
