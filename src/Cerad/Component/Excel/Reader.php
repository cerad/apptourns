<?php
/* ==================================================
 * Wrap interface to the excel spreasheet processing
 * 
 * 23 Sep 2013
 * Think this is depreciated 
 * Use Excel::createReaderForFile or Excel::load
 */
namespace Cerad\Component\Excel;

class Reader
{
    protected function createReaderForFile($fileName,$readDataOnly = true)
    {
        // Most common case
        $reader = new \PHPExcel_Reader_Excel5();
        
        $reader->setReadDataOnly($readDataOnly);
        
        if ($reader->canRead($fileName)) return $reader;
 
        // Make sure have zip archive
        if (class_exists('ZipArchive')) 
        {
            $reader = new \PHPExcel_Reader_Excel2007();
        
            $reader->setReadDataOnly($readDataOnly);
        
            if ($reader->canRead($fileName)) return $reader;
        }
        
        // Note that csv does not actually check for a csv file
        $reader = new \PHPExcel_Reader_CSV();
        
        if ($reader->canRead($fileName)) return $reader;
        
        throw new \Exception("No Reader found for $fileName");

    }
    public function load($fileName, $readDataOnly = true)
    {
        $reader = $this->createReaderForFile($fileName,$readDataOnly);

        return $reader->load($fileName);
    }
    public function loadx($file)
    {
        return \PHPExcel_IOFactory::load($file);
    }
    /* ==================================================
     * 15 Oct 2013
     * Put these in here as well, hack for Kicks
     * Tested on: xlsx
     */
    public function processTime($time)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($time,'hh:mm:ss');
    }
    public function processDate($date)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($date,'yyyy-MM-dd');
    }
}
?>