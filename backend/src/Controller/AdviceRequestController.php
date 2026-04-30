<?php

namespace App\Controller;

use App\Entity\AdviceRequest;
use App\Repository\AdviceRequestRepository;
use App\Service\RuleEngine\StackRecommender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api', name: 'api_')]
class AdviceRequestController extends AbstractController
{
    public function __construct(
        private AdviceRequestRepository $adviceRepository,
        private StackRecommender $stackRecommender,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/advice-requests', name: 'advice_create', methods: ['POST'])]
    public function createAdviceRequest(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            // Validation
            $errors = [];

            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }

            if (empty($data['subject'])) {
                $errors['subject'] = 'Subject is required';
            }

            if (empty($data['message'])) {
                $errors['message'] = 'Message is required';
            }

            if (!empty($errors)) {
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            // Create advice request
            $adviceRequest = new AdviceRequest();
            $adviceRequest->setEmail($data['email']);
            $adviceRequest->setSubject($data['subject']);
            $adviceRequest->setMessage($data['message']);
            $adviceRequest->setName($data['name'] ?? null);
            $adviceRequest->setRecommendationId($data['recommendationId'] ?? null);
            $adviceRequest->setStatus('pending');

            // Optionally save questionnaire snapshot
            if (!empty($data['questionnaireSnapshot'])) {
                $adviceRequest->setQuestionnaireSnapshot(json_encode($data['questionnaireSnapshot']));
            }

            $this->entityManager->persist($adviceRequest);
            $this->entityManager->flush();

            return $this->json([
                'id' => $adviceRequest->getId(),
                'email' => $adviceRequest->getEmail(),
                'status' => $adviceRequest->getStatus(),
                'createdAt' => $adviceRequest->getCreatedAt()->format('c'),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to create advice request',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/advice-requests/{id}', name: 'advice_get', methods: ['GET'])]
    public function getAdviceRequest(int $id): JsonResponse
    {
        try {
            $adviceRequest = $this->adviceRepository->find($id);

            if (!$adviceRequest) {
                return $this->json(['error' => 'Advice request not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'id' => $adviceRequest->getId(),
                'name' => $adviceRequest->getName(),
                'email' => $adviceRequest->getEmail(),
                'subject' => $adviceRequest->getSubject(),
                'message' => $adviceRequest->getMessage(),
                'status' => $adviceRequest->getStatus(),
                'recommendationId' => $adviceRequest->getRecommendationId(),
                'createdAt' => $adviceRequest->getCreatedAt()->format('c'),
                'answeredAt' => $adviceRequest->getAnsweredAt()?->format('c'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve advice request',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/advice-requests', name: 'advice_list', methods: ['GET'])]
    public function listAdviceRequests(Request $request): JsonResponse
    {
        try {
            $status = $request->query->get('status');
            $email = $request->query->get('email');

            if ($status) {
                $requests = $this->adviceRepository->findByStatus($status);
            } elseif ($email) {
                $requests = $this->adviceRepository->findByEmail($email);
            } else {
                $requests = $this->adviceRepository->findAll();
            }

            $data = array_map(fn($req) => [
                'id' => $req->getId(),
                'name' => $req->getName(),
                'email' => $req->getEmail(),
                'subject' => $req->getSubject(),
                'status' => $req->getStatus(),
                'createdAt' => $req->getCreatedAt()->format('c'),
            ], $requests);

            return $this->json([
                'total' => count($data),
                'requests' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to list advice requests',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/technologies', name: 'technologies_list', methods: ['GET'])]
    public function getTechnologies(): JsonResponse
    {
        try {
            $technologies = $this->stackRecommender->getTechnologies();

            // Group by category
            $grouped = [];
            foreach ($technologies as $tech) {
                $category = $tech['category'] ?? 'other';
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $tech;
            }

            return $this->json([
                'total' => count($technologies),
                'categories' => array_keys($grouped),
                'technologies' => $grouped,
                'all' => $technologies,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve technologies',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/technologies/{id}', name: 'technology_get', methods: ['GET'])]
    public function getTechnology(string $id): JsonResponse
    {
        try {
            $technology = $this->stackRecommender->getTechnologyById($id);

            if (!$technology) {
                return $this->json(['error' => 'Technology not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($technology);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve technology',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/compare', name: 'compare', methods: ['POST'])]
    public function compareTechnologies(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['ids']) || !is_array($data['ids'])) {
                return $this->json(['error' => 'Invalid request. Expected array of technology IDs'], Response::HTTP_BAD_REQUEST);
            }

            if (count($data['ids']) < 2) {
                return $this->json(['error' => 'At least 2 technologies are required for comparison'], Response::HTTP_BAD_REQUEST);
            }

            if (count($data['ids']) > 5) {
                return $this->json(['error' => 'Maximum 5 technologies can be compared'], Response::HTTP_BAD_REQUEST);
            }

            $comparison = $this->stackRecommender->compareTechnologies($data['ids']);

            return $this->json([
                'technologies' => $comparison['technologies'],
                'comparison' => $comparison['comparison'],
                'count' => count($comparison['technologies']),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to compare technologies',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'timestamp' => date('c'),
            'service' => 'ProjectAdvisor API',
        ]);
    }
}
