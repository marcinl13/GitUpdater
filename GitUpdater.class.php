<?php

class GitUpdater
{
  private $repository = '';
  private $account = '';
  private $gitData = array();
  private $locData = array();
  private $downloadPath = '';
  private $localPath = '';
  private $err = array();

  function __construct()
  { }

  public function GitConnect($rep, $acc)
  {
    if (mb_strlen($rep) == 0) $this->err[] = 'repository';
    if (mb_strlen($acc) == 0) $this->err[] = 'account';

    $this->repository = $rep;
    $this->account = $acc;

    if (is_array($this->err) && empty($this->err)) {
      $this->gitData = json_decode(file_get_contents(
        "https://api.github.com/repos/{$acc}/{$rep}/releases",
        false,
        stream_context_create(['http' => ['header' => "User-Agent: Vestibulum\r\n"]])
      ), true);
    }
  }

  public function LocalData($path)
  {
    if (mb_strlen($path) == 0) $this->err[] = 'path';

    if (is_array($this->err) && empty($this->err)) {
      $this->localPath = $path;
      $this->locData = json_decode(file_get_contents($path), true);
    }
  }

  public function CheckForUpdates()
  {
    if (is_array($this->err) && empty($this->err)) {
      $latestVersionGit = $this->gitData[0]['tag_name'];
      $latestInstallVersion  = $this->locData[0]['version'];

      $version1 =  intval(join("", explode(".", $latestInstallVersion)));
      $version2 =  intval(join("", explode(".", $latestVersionGit)));

      return ($version1 >= $version2) ? false : true;
    } else {
      $this->err[] = 'update fail';
    }
  }

  public function DownloadFile($dp)
  {
    if (mb_strlen($dp) == 0) $this->err[] = 'downloadPath';

    if (self::CheckForUpdates() == true) {
      $latestVersionGit = $this->gitData[0]['tag_name'];
      $fileName = "{$this->repository}-{$latestVersionGit}.zip";
      $downloadLink = "https://github.com/{$this->account}/{$this->repository}/archive/{$latestVersionGit}.zip";

      $this->downloadPath = $dp . $fileName;

      $fileData = file_get_contents($downloadLink);

      fopen($dp . $fileName, 'w');

      // Save Content to file
      $downloaded = file_put_contents($dp . $fileName, $fileData);

      if ($downloaded == 0) {
        $this->err[] = 'can\'t download';
      }
    }
  }

  public function UnZip($location)
  {
    if (mb_strlen($this->downloadPath)>0) {
      $zip = new ZipArchive;
      $res = $zip->open($this->downloadPath);

      if ($res === true) {
        $zip->extractTo($location);
        $zip->close();
        self::overrideLocal();
      } else {
        $this->err[] = 'UnZip error';
      }
    }
  }

  public function ShowErrors()
  {
    return $this->err;
  }

  private function overrideLocal()
  {
    self::LocalData($this->localPath);

    if (self::CheckForUpdates() == false) {
      $this->locData[0]['version'] = intval(join("", explode(".", $this->gitData[0]['tag_name'])));

      //rewrite json
      $fp = fopen($this->localPath, 'w');
      fwrite($fp, json_encode($this->locData));
      fclose($fp);
    }
  }
}
