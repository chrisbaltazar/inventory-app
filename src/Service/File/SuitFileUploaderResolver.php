<?php

namespace App\Service\File;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SuitFileUploaderResolver implements ValueResolverInterface
{

    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== SuitFileUploader::class) {
            return [];
        }

        return [
            new SuitFileUploader(
                targetDirectory: $this->params->get('suit_pictures_dir'),
                fileName: new FileNameHash(),
            ),
        ];
    }
}