<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Point;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class TransactionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/transactions', name: 'create_transaction', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (empty($data['member_id']) || empty($data['amount'])) {
            return $this->json([
                'success' => false,
                'message' => 'member_id và amount là bắt buộc'
            ], 400);
        }

        if ($data['amount'] <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'amount phải lớn hơn 0'
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

        // Lấy ví của member
        $walletId = $member->getWallet()?->getId();
        $wallet = $member->getWallet();
        if (!$wallet) {
            return $this->json([
                'success' => false,
                'message' => 'Ví không tồn tại'
            ], 404);
        }

        // Bắt đầu Database Transaction
        $this->em->beginTransaction();

        try {
            // Lock wallet trước khi đọc balance — tránh race condition
            $wallet = $this->em->getRepository(\App\Entity\Wallet::class)->find(
                $walletId,
                \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE
            );

            if (!$wallet) {
                $this->em->rollback();
                return $this->json([
                    'success' => false,
                    'message' => 'Ví không tồn tại'
                ], 404);
            }
            // Bước 1: Tạo transaction
            $transaction = new Transaction();
            $transaction->setMember($member);
            $transaction->setAmount((string) $data['amount']);
            $transaction->setStatus('completed');
            $this->em->persist($transaction);
            $this->em->flush();

            // Bước 2: Tính điểm thưởng (1% * amount)
            $pointAmount = (int) round($data['amount'] * 0.01);

            // Bước 3: Tạo bản ghi points
            $point = new Point();
            $point->setWallet($wallet);
            $point->setTransaction($transaction);
            $point->setPointAmount($pointAmount);
            $point->setDescription("Tích điểm từ giao dịch #{$transaction->getId()} - Số tiền: {$data['amount']}");
            $this->em->persist($point);

            // Bước 4: Cập nhật balance
            $newBalance = (string) ((float) $wallet->getBalance() + $pointAmount);
            $wallet->setBalance($newBalance);
            $this->em->persist($wallet);

            $this->em->flush();
            $this->em->commit();

            return $this->json([
                'success'      => true,
                'message'      => 'Giao dịch thành công',
                'data'         => [
                    'transaction_id' => $transaction->getId(),
                    'member_id'      => $member->getId(),
                    'amount'         => $data['amount'],
                    'points_earned'  => $pointAmount,
                    'new_balance'    => $newBalance,
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
