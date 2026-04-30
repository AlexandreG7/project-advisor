<?php

namespace App\Controller;

use App\Entity\Recommendation;
use App\Repository\RecommendationRepository;
use App\Service\RuleEngine\StackRecommender;
use App\Service\MarkdownGenerator\MarkdownGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/recommendations', name: 'recommendations_')]
class RecommendationController extends AbstractController
{
    public function __construct(
        private RecommendationRepository $recommendationRepository,
        private StackRecommender $stackRecommender,
        private MarkdownGenerator $markdownGenerator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            // Generate recommendation
            $lang = in_array($data['lang'] ?? 'fr', ['fr', 'en']) ? ($data['lang'] ?? 'fr') : 'fr';
            $result = $this->stackRecommender->recommend($data, $lang);

            // Save to database
            $recommendation = new Recommendation();
            $recommendation->setAnswersJson(json_encode($data));
            $recommendation->setResultJson(json_encode($result));

            $this->entityManager->persist($recommendation);
            $this->entityManager->flush();

            return $this->json([
                'id' => $recommendation->getId(),
                'result' => $result,
                'createdAt' => $recommendation->getCreatedAt()->format('c'),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate recommendation',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id, Request $request): JsonResponse
    {
        try {
            $recommendation = $this->recommendationRepository->findById($id);

            if (!$recommendation) {
                return $this->json(['error' => 'Recommendation not found'], Response::HTTP_NOT_FOUND);
            }

            $lang = in_array($request->query->get('lang', 'fr'), ['fr', 'en'])
                ? $request->query->get('lang', 'fr')
                : 'fr';

            $answers = $recommendation->getAnswers();
            $result = $this->stackRecommender->recommend($answers, $lang);

            return $this->json([
                'id' => $recommendation->getId(),
                'result' => $result,
                'answers' => $answers,
                'createdAt' => $recommendation->getCreatedAt()->format('c'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve recommendation',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/files', name: 'files', methods: ['GET'])]
    public function files(string $id): JsonResponse
    {
        try {
            $recommendation = $this->recommendationRepository->findById($id);

            if (!$recommendation) {
                return $this->json(['error' => 'Recommendation not found'], Response::HTTP_NOT_FOUND);
            }

            $result = $recommendation->getResult();
            $files = $this->markdownGenerator->generateFiles($result);

            $response = [];
            foreach ($files as $name => $content) {
                $response[] = [
                    'name' => $name,
                    'content' => $content,
                    'size' => strlen($content),
                ];
            }

            return $this->json([
                'id' => $recommendation->getId(),
                'files' => $response,
                'count' => count($response),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate files',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/download', name: 'download', methods: ['GET'])]
    public function download(string $id): Response
    {
        try {
            $recommendation = $this->recommendationRepository->findById($id);

            if (!$recommendation) {
                return new JsonResponse(['error' => 'Recommendation not found'], Response::HTTP_NOT_FOUND);
            }

            $result = $recommendation->getResult();
            $files = $this->markdownGenerator->generateFiles($result);

            // Create ZIP file in memory
            $tmpFile = tempnam(sys_get_temp_dir(), 'projectadvisor_');
            $zip = new \ZipArchive();
            $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $name => $content) {
                $zip->addFromString($name, $content);
            }

            // Add metadata file
            $metadata = [
                'id' => $recommendation->getId(),
                'generatedAt' => date('c'),
                'answers' => $recommendation->getAnswers(),
            ];
            $zip->addFromString('METADATA.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $zip->close();

            // Create streamed response
            $response = new StreamedResponse(function () use ($tmpFile) {
                readfile($tmpFile);
            });

            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment; filename="ProjectAdvisor-' . $recommendation->getId() . '.zip"');
            $response->headers->set('Content-Length', filesize($tmpFile));

            // Delete temp file after sending
            register_shutdown_function(function () use ($tmpFile) {
                if (file_exists($tmpFile)) {
                    unlink($tmpFile);
                }
            });

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to generate ZIP',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
