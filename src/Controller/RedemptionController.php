<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\Member;
use App\Entity\Point;
use App\Entity\Redemption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class RedemptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/redemptions', name: 'create_redemption', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (empty($data['member_id']) || empty($data['gift_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'member_id và gift_id là bắt buộc'
            ], 400);
        }

        // Tìm member
        $member = $this->em->getRepository(Member::class)->find($data['member_id']);
        if (!$member) {
            return $this->json([
                'success' => false,
                'message' => 'Member không tồn tại'
            ], 404);
        }

        $this->em->beginTransaction();

        try {
            // Bước 1: Kiểm tra quà - dùng PESSIMISTIC_WRITE lock để tránh race condition
            $gift = $this->em->getRepository(Gift::class)->find(
                $data['gift_id'],
                \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE
            );

            if (!$gift) {
                $this->em->rollback();
                return $this->json([
                    'success' => false,
                    'message' => 'Quà không tồn tại'
                ], 404);
            }

            if ($gift->getStatus() !== 'active') {
                $this->em->rollback();
                return $this->json([
                    'success' => false,
                    'message' => 'Quà không còn khả dụng'
                ], 400);
            }

            if ($gift->getStock() <= 0) {
                $this->em->rollback();
                return $this->json([
                    'success' => false,
                    'message' => 'Quà đã hết hàng'
                ], 400);
            }

            // Bước 2: Kiểm tra số dư ví - cũng lock để tránh race condition
            $wallet = $this->em->getRepository(\App\Entity\Wallet::class)->find(
                $member->getWallet()->getId(),
                \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE
            );

            if ((float) $wallet->getBalance() < $gift->getPointCost()) {
                $this->em->rollback();
                return $this->json([
                    'success' => false,
                    'message' => 'Số dư điểm không đủ',
                    'data'    => [
                        'current_balance' => $wallet->getBalance(),
                        'required_points' => $gift->getPointCost(),
                    ]
                ], 400);
            }

            // Bước 3a: Giảm stock quà
            $gift->setStock($gift->getStock() - 1);
            $this->em->persist($gift);

            // Bước 3b: Tạo redemption
            $redemption = new Redemption();
            $redemption->setMember($member);
            $redemption->setGift($gift);
            $redemption->setPointsUsed($gift->getPointCost());
            $redemption->setStatus('completed');
            $this->em->persist($redemption);

            // Bước 3c: Tạo point âm
            $point = new Point();
            $point->setWallet($wallet);
            $point->setRedemption($redemption);
            $point->setPointAmount(-$gift->getPointCost());
            $point->setDescription("Đổi quà: {$gift->getGiftName()}");
            $this->em->persist($point);

            // Bước 3d: Cập nhật balance
            $newBalance = (string) ((float) $wallet->getBalance() - $gift->getPointCost());
            $wallet->setBalance($newBalance);
            $this->em->persist($wallet);

            $this->em->flush();
            $this->em->commit();

            return $this->json([
                'success' => true,
                'message' => 'Đổi quà thành công',
                'data'    => [
                    'redemption_id' => $redemption->getId(),
                    'member_id'     => $member->getId(),
                    'gift_name'     => $gift->getGiftName(),
                    'points_used'   => $gift->getPointCost(),
                    'new_balance'   => $newBalance,
                ]
            ], 201);

        } catch (\Exception $e) {
            $this->em->rollback();
            return $this->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}