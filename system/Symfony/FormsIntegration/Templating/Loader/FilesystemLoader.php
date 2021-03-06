<?php
namespace ED\Symfony\FormsIntegration\Templating\Loader;

use Symfony\Component\Templating\Loader\FilesystemLoader as BaseFilesystemLoader;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

class FilesystemLoader extends BaseFilesystemLoader
{

    /**
     * {@inheritdoc}
     */
    public function load(TemplateReferenceInterface $template)
    {
        $file = $template->getPath();
        var_dump($file);

        if (self::isAbsolutePath($file) && is_file($file)) {
            return new FileStorage($file);
        }

        $replacements = array();
        foreach ($template->all() as $key => $value) {
            $replacements['%'.$key.'%'] = $value;
        }

        $fileFailures = array();
        foreach ($this->templatePathPatterns as $templatePathPattern) {
            if (is_file($file = strtr($templatePathPattern, $replacements)) && is_readable($file)) {
                if (null !== $this->logger) {
                    $this->logger->debug('Loaded template file.', array('file' => $file));
                }

                return new FileStorage($file);
            }

            if (null !== $this->logger) {
                $fileFailures[] = $file;
            }
        }

        // only log failures if no template could be loaded at all
        foreach ($fileFailures as $file) {
            if (null !== $this->logger) {
                $this->logger->debug('Failed loading template file.', array('file' => $file));
            }
        }

        return false;
    }

}