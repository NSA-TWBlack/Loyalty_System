<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\PointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class WalletController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PointRepository $pointRepository
    ) {}

    #[Route('/members/{member_id}/wallet', name: 'get_wallet', methods: ['GET'])]
    public function show(int $member_id): JsonResponse
    {
        // Tìm member
        $member = $this->em->getRepository(Member::class)->find($member_id);
        if (!$member) {
            return $this->json([
                'success' => false,
                'message' => 'Member không tồn tại'
            ], 404);
        }

        $wallet = $member->getWallet();
        if (!$wallet) {
            return $this->json([
                'success' => false,
                'message' => 'Ví không tồn tại'
            ], 404);
        }

        // Lấy 10 lịch sử điểm gần nhất
        $recentPoints = $this->pointRepository->findRecentByWallet($wallet->getId(), 10);

        $pointHistory = array_map(function ($point) {
            return [
                'id'           => $point->getId(),
                'point_amount' => $point->getPointAmount(),
                'description'  => $point->getDescription(),
                'created_at'   => $point->getCreatedAt()->format('Y-m-d H:i:s'),
                'type'         => $point->getPointAmount() > 0 ? 'earn' : 'redeem',
            ];
        }, $recentPoints);

        return $this->json([
            'success' => true,
            'data'    => [
                'member' => [
                    'id'         => $member->getId(),
                    'fullname'   => $member->getFullname(),
                    'email'      => $member->getEmail(),
                    'created_at' => $member->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
                'wallet' => [
                    'id'         => $wallet->getId(),
                    'balance'    => $wallet->getBalance(),
                    'updated_at' => $wallet->getUpdatedAt()->format('Y-m-d H:i:s'),
                ],
                'point_history' => $pointHistory,
            ]
        ]);
    }
}