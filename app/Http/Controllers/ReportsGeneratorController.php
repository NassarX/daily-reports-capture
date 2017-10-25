<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class ReportsGeneratorController extends Controller
{
	/** @var \Spatie\Browsershot\Browsershot  */
    private $browserShot;

    /** @var Url of report to be captured */
    private $reportUrl;

    /** @var Company directory */
    private $companyDir;

    /** @var Reports directory */
    private $targetDir;

    /** @var Generated file path */
    private $targetFile;

    /** @var Returned file path after public */
    private $filePath;

	/**
	 * ReportsGeneratorController constructor.
	 *
	 * @param \Spatie\Browsershot\Browsershot $browsershot
	 */
    public function __construct(Browsershot $browsershot)
    {
    	$this->browserShot = $browsershot;
    }

	/**
	 * Set Up required paths
	 * @return void
	 */
    private function setUpPaths()
    {
	    $this->reportUrl = request()->url;
	    $this->companyDir = array_last(explode("/", $this->reportUrl));
	    $this->targetDir = public_path('reports/').$this->companyDir;

	    !file_exists($this->targetDir)? mkdir($this->targetDir): null;

	    $this->targetFile = $this->targetDir.'/'.$this->companyDir.'-'.Carbon::today()->timestamp.'.png';
	    $this->filePath = 'reports/'.$this->companyDir.'-'.Carbon::today()->timestamp.'.png';
    }

	/**
	 * Generate report screenShot
	 * @return $this
	 */
    public function generate()
    {
    	$this->setUpPaths();

	    $status = $this->capture($this->reportUrl, $this->targetFile);

		if (is_null($status)) {
			return response()->json([
				'report' => $this->filePath
			])->setStatusCode(200);
		} else {
			return response()->json([
				'report' => 'error occurred !'
			])->setStatusCode(500);
		}
    }

	/**
	 * @param $reportUrl
	 * @param $targetFile
	 * @return string
	 */
    public function capture($reportUrl, $targetFile)
    {
	    $status = $this->browserShot
		    ->setUrl($reportUrl)
		    ->setNodeBinary('/usr/local/bin/node')
		    ->setNpmBinary('/usr/local/bin/npm')
		    ->fullPage()
		    ->timeout(0)
		    ->setNetworkIdleTimeout('6000')
		    ->windowSize(1920, 1080)
		    ->save($targetFile);

	    return $status;
    }
}
