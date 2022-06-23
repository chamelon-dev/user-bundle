<?php

namespace Pantheon\UserBundle\Controller;

use Pantheon\UserBundle\Service\DirService;
use Pantheon\UserBundle\Service\FileService;
use Pantheon\UserBundle\Service\StringService;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TODO: подключить guzzlehttp/guzzle в бандл
 *
 * @Route("/watermark")
 */
class WaterMarkController extends AbstractController
{
    public function __construct(
        ClientInterface $client,
        KernelInterface $kernel,
        DirService $dirService,
        StringService $stringService,
        FileService $fileService
    )
    {
        $this->client = $client;
        $this->kernel = $kernel;
        $this->dirService = $dirService;
        $this->stringService = $stringService;
        $this->fileService = $fileService;

        $this->host = 'http://host:8090';
        $this->host = 'http://fias-bridge'; // для отладки
        $this->url = '/watermark/catch/';
        $this->bundleDir = $this->kernel->locateResource('@UserBundle');
        $projectDir = $this->kernel->getProjectDir();
        $this->imageDir = $projectDir . '/var/image';
        $this->documentDir = $projectDir . '/var/document';
    }

    /**
     * Поиск документа на фото, определение его границ.
     *
     * @Route("/first/", name="watermark_first")
     */
    public function first(Request $request)
    {
        $route = '/search';
        $route = '/watermark/catch/';
        $url = $this->stringService->createPath($this->host, $route) . '/';
        $imagesList = $this->dirService->getFilesList($this->imageDir);
        $imageName = $imagesList[array_rand($imagesList)];;
        $imagePath = $this->stringService->createSlashedPath($this->imageDir, $imageName);
        $base64 = $this->fileService->toBase64($imagePath);
        $post = [
            'image' => $base64,
        ];
        try {
            $response = $this->client->request(
                'POST',
                $url,
                [
                    'headers' => $this->headers ?? [],
                    'form_params' => $post,
                ]
            );
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
        $result = $response->getBody()->getContents();
        return new Response($result);
    }

    /**
     * Распознавание водяного знака, поиск человека.
     *
     * @Route("/second/", name="watermark_second")
     */
    public function second(Request $request)
    {
        $route = '/person_detector';
        $route = '/watermark/catch/';
        $url = $this->stringService->createPath($this->host, $route) . '/';
        $imagesList = $this->dirService->getFilesList($this->imageDir);
        $imageName = $imagesList[array_rand($imagesList)];;
        $imagePath = $this->stringService->createSlashedPath($this->imageDir, $imageName);
        $base64 = $this->fileService->toBase64($imagePath);
        $post = [
            'imageData' => $base64,
            'coords' => $bboxes ?? 'null',
        ];
        try {
            $response = $this->client->request(
                'POST',
                $url,
                [
                    'headers' => $this->headers ?? [],
                    'form_params' => $post,
                ]
            );
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
        $result = $response->getBody()->getContents();
        return new Response($result);
    }

    /**
     * Генерация водяного знака.
     *
     * @Route("/third/", name="watermark_third")
     */
    public function third(Request $request)
    {
        $documentId = null;
        $route = '/process';
        $route = '/watermark/catch/';
        $names = ['Иван Иванов', 'Петр Петров', 'Сидор Сидоров'];
        $url = $this->stringService->createPath($this->host, $route) . '/';
        if ($documentId) {
            $post = [
                'doc_id' => $documentId,
                'names' => $names ?? [],
            ];

        } else {
            $documentList = $this->dirService->getFilesList($this->documentDir);
            $documentName = $documentList[array_rand($documentList)];;
            $documentPath = $this->stringService->createSlashedPath($this->documentDir, $documentName);
            $base64 = $this->fileService->toBase64($documentPath);
            $post = [
                'document' => $base64,
                'names' => $names ?? [],
            ];
        }
        try {
            $response = $this->client->request(
                'POST',
                $url,
                [
                    'headers' => $this->headers ?? [],
                    'form_params' => $post,
                ]
            );
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
        $result = $response->getBody()->getContents();
        return new Response($result);
    }

    /**
     * Временный роут для приема запросов.
     *
     * @Route("/catch/", name="catch")
     */
    public function catch(Request $request) : JsonResponse
    {
        $post = $request->request->all();
        $get = $request->query->all();
        dump('html-результат с роута catch --> ', 'POST', $post, 'GET', $get);
        die();
    }
}
