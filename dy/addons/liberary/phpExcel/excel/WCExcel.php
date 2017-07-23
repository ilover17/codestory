<?php
date_default_timezone_set('Etc/GMT-8');
include_once dirname(__FILE__) . '/PHPExcel.php';

class WCExcel
{
	public $objPHPExcel;

	public function __construct()
	{
		$this->objPHPExcel = new PHPExcel();
	}

	public function export($content, $title = '报表', $savePath = null)
	{
		$objPHPExcel = $this->generatePHPObj($content, $title);

		$savePath = $this->saveFile($objPHPExcel, $savePath);

		return $savePath;
	}

	public function generatePHPObj($content, $title){
		$objPHPExcel = $this->objPHPExcel;

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2003 XLSX Test Document")
									 ->setSubject("Office 2003 XLSX Test Document")
									 ->setDescription("Test document for Office 2003 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2003 openxml php")
									 ->setCategory("Test result file");

		// Set default font
		$objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);


		// Add some data, resembling some different data types
		$trid=1;
		foreach($content as $tr){	
			$tdtotal=0;
			foreach($tr as $td){
				$tdid=(intval($tdtotal/26)>=1)?chr(ord('A')+intval($tdtotal/26)-1).chr(ord('A')+$tdtotal%26):chr(ord('A')+$tdtotal%26);

				//$objPHPExcel->getActiveSheet()->setCellValue($tdid.$trid,$td);
				//统一设置成文本格式
				$objPHPExcel->getActiveSheet()->setCellValueExplicit($tdid.$trid,$td,PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getStyle($tdid.$trid)->getNumberFormat()->setFormatCode("@");
				$tdtotal++;
			}
			$trid++;
		}

		//Set Width
		for($i=0;$i<$tdtotal;$i++){
			$tdid=(intval($i/26)>=1)?chr(ord('A')+intval($i/26)-1).chr(ord('A')+$i%26):chr(ord('A')+$i%26);
			$objPHPExcel->getActiveSheet()->getColumnDimension($tdid)->setWidth(12);      
		}

		// Rename sheet
		$title = substr($title,0,30);
		$objPHPExcel->getActiveSheet()->setTitle($title);	

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);	

		return $objPHPExcel;
	}

	public function saveFile($objPHPExcel, $savePath){
		// Save Excel 2003 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$savePath  = $savePath ? $savePath : DATA_PATH . '/export/' . date('YmdHis').'.xls';
		$objWriter->save($savePath);

		return $savePath;
	}
	public function import()
	{
		return;
	}
}