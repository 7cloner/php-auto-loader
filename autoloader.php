<?php

if(class_exists('ClonerDirectoryManager')){
    return;
}

class ClonerDirectoryManager
{

    private string $rootPath;

    public function __construct(string $rootPath = __DIR__)
    {
        $this->rootPath = $rootPath;
    }

    private function findDirectories(string $path): array
    {
        if (is_dir($path)) {
            $directories = [$path];
            return array_merge($directories, $this->getDirectorySubFolders($path));
        }

        return [];
    }

    private function getDirectorySubFolders(string $path): array
    {
        if (is_dir($path)) {
            $filesAndFoldersList = scandir($path);
            if (is_countable($filesAndFoldersList) && count($filesAndFoldersList)) {
                $folders = [];

                foreach ($filesAndFoldersList as $fileOrFolderName){
                    $thePath = $path . DIRECTORY_SEPARATOR . $fileOrFolderName;

                    if(!in_array($fileOrFolderName, ['.', '..']) && is_dir($thePath)){
                        $folders[] = $thePath;
                        $folders = array_merge($this->getDirectorySubFolders($thePath), $folders);
                    }
                }
                return $folders;
            }
        }
        return [];
    }

    public function getDirectories(): array
    {
        return $this->findDirectories($this->rootPath);
    }
}

class ClonerFileFounder {
    
    private array $directories;
    
    public function __construct(array $directories)
    {
        $this->directories = $directories;
    }

    private function getDirectoryFilesName(string $directoryPath): array {
        $filesName = [];

        if(is_dir($directoryPath)){
            $filesAndFolders = scandir($directoryPath);
            foreach ($filesAndFolders as $fileOrFolder) {
                $thePath = $directoryPath . DIRECTORY_SEPARATOR . $fileOrFolder;
                if(is_file($thePath) && file_exists($thePath) && is_readable($thePath)){
                    $filesName[] = $fileOrFolder;
                }
            }
        }

        return $filesName;
    }

    private function findDirectorySimilarFiles(string $directory, string $targetFileName): array
    {
        $similarFiles = [];
        $filesName = $this->getDirectoryFilesName($directory);
        foreach ($filesName as $fileName) {
            $onlyFileName = explode('.', $fileName)[0];
            $smallTargetFileName = strtolower($targetFileName);

            if($onlyFileName == $targetFileName || $smallTargetFileName == $onlyFileName ||
                str_contains($fileName, $targetFileName) || str_contains($fileName, $smallTargetFileName)){

                $similarFiles[] = $directory . DIRECTORY_SEPARATOR . $fileName;
            }
        }

        return $similarFiles;
    }

    public function findSimilarFilesPath($name): array
    {
        $paths = [];

        foreach ($this->directories as $directory){
            $paths = array_merge($paths, $this->findDirectorySimilarFiles($directory, $name));
        }

        return $paths;
    }
}

class ClonerAutoloader
{

    private array $directories;
    private ClonerFileFounder $fileFounder;

    public function __construct(string $targetDirectory)
    {
        try {
            $this->directories = (new ClonerDirectoryManager($targetDirectory))->getDirectories();
            $this->fileFounder = new ClonerFileFounder($this->directories);
            
            spl_autoload_register(function ($fileName){
                $files = $this->fileFounder->findSimilarFilesPath($fileName);

                if (count($files)) {
                    $this->loadFiles($files);
                }
            });
        } catch (Exception) {
        }
    }

    private function loadFiles(array $filesPath): void
    {
        foreach ($filesPath as $filePath) {
            if ($this->fileIsValid($filePath)) {
                require_once $filePath;
            }
        }
    }

    private function fileIsValid(string $filePath): bool
    {
        return is_file($filePath) && file_exists($filePath) && is_readable($filePath);
    }

}