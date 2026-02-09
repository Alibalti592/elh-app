<?php

namespace App\Entity;

use App\Repository\JeunRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JeunRepository::class)]
class Jeun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbDays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $selectedYear = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jeunNbDaysR = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $years = null;

    public function __construct()
    {
        $this->nbDays = 0;
        $this->jeunNbDaysR = 0;
        $date = new \DateTime();
        $this->selectedYear = intval($date->format('Y'));
        $this->text = '';
        $this->years = [
            [
                'year' => $this->selectedYear,
                'total' => 0,
                'done' => 0,
            ]
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbDays(): ?int
    {
        return $this->nbDays;
    }

    public function setNbDays(int $nbDays): static
    {
        $this->nbDays = $nbDays;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getSelectedYear(): ?int
    {
        return $this->selectedYear;
    }

    public function setSelectedYear(int $selectedYear): static
    {
        $this->selectedYear = $selectedYear;

        return $this;
    }

    public function getJeunNbDaysR(): ?int
    {
        return $this->jeunNbDaysR;
    }

    public function setJeunNbDaysR(int $jeunNbDaysR): static
    {
        $this->jeunNbDaysR = $jeunNbDaysR;

        return $this;
    }

    public function getYears(): array
    {
        $years = $this->years ?? [];
        if ($years === []) {
            $fallbackYear = $this->selectedYear ?? null;
            if (!is_null($fallbackYear)) {
                return [[
                    'year' => (int) $fallbackYear,
                    'total' => (int) ($this->nbDays ?? 0),
                    'done' => (int) ($this->jeunNbDaysR ?? 0),
                ]];
            }
            return [];
        }

        return $this->normalizeYears($years);
    }

    public function setYears(?array $years): static
    {
        if (is_null($years)) {
            $this->years = null;
            return $this;
        }
        $normalized = $this->normalizeYears($years);
        $this->years = $normalized === [] ? null : $normalized;
        return $this;
    }

    public function mergeYearEntry(int $year, int $total, int $done): static
    {
        $entries = $this->getYears();
        $found = false;
        foreach ($entries as $index => $entry) {
            if ((int) $entry['year'] === $year) {
                $entries[$index]['total'] = max(0, $total);
                $entries[$index]['done'] = max(0, min($total, $done));
                $found = true;
                break;
            }
        }
        if (!$found) {
            $entries[] = [
                'year' => $year,
                'total' => max(0, $total),
                'done' => max(0, min($total, $done)),
            ];
        }
        $this->years = $this->normalizeYears($entries);
        return $this;
    }

    public function syncLegacyFieldsFromYears(?int $selectedYear = null): void
    {
        $entries = $this->getYears();
        if ($entries === []) {
            return;
        }

        $targetYear = $selectedYear ?? $this->selectedYear ?? null;
        $entry = null;
        if (!is_null($targetYear)) {
            foreach ($entries as $candidate) {
                if ((int) $candidate['year'] === (int) $targetYear) {
                    $entry = $candidate;
                    break;
                }
            }
        }

        if (is_null($entry)) {
            $entry = $entries[0];
            $targetYear = (int) $entry['year'];
        }

        $this->selectedYear = (int) $targetYear;
        $this->nbDays = (int) ($entry['total'] ?? 0);
        $this->jeunNbDaysR = (int) ($entry['done'] ?? 0);
    }

    public function getRemainingDaysByYear(): array
    {
        $remaining = [];
        foreach ($this->getYears() as $entry) {
            $year = (int) $entry['year'];
            $total = (int) ($entry['total'] ?? 0);
            $done = (int) ($entry['done'] ?? 0);
            $rest = max(0, $total - $done);
            if ($rest > 0) {
                $remaining[$year] = $rest;
            }
        }
        ksort($remaining);
        return $remaining;
    }

    public function getTotalRemainingDays(): int
    {
        return array_sum($this->getRemainingDaysByYear());
    }

    public function getRemainingDaysSummary(): string
    {
        $remainingByYear = $this->getRemainingDaysByYear();
        $total = array_sum($remainingByYear);
        if ($total <= 0) {
            return "Aucun jour à rattraper";
        }

        if (count($remainingByYear) === 1) {
            $year = array_key_first($remainingByYear);
            $days = $remainingByYear[$year];
            $label = $days > 1 ? 'jours' : 'jour';
            return $days . " {$label} à rattraper pour " . $year;
        }

        $parts = [];
        foreach ($remainingByYear as $year => $days) {
            $label = $days > 1 ? 'jours' : 'jour';
            $parts[] = $days . " {$label} (" . $year . ")";
        }
        $label = $total > 1 ? 'jours' : 'jour';
        return $total . " {$label} à rattraper (" . implode(', ', $parts) . ")";
    }

    private function normalizeYears(array $years): array
    {
        $map = [];
        foreach ($years as $key => $value) {
            $entry = null;
            if (is_array($value)) {
                $entry = $value;
                if (!isset($entry['year']) && is_numeric($key)) {
                    $entry['year'] = (int) $key;
                }
            } elseif (is_numeric($key)) {
                $entry = ['year' => (int) $key, 'total' => (int) $value];
            }

            if (!is_array($entry)) {
                continue;
            }

            $year = isset($entry['year']) ? (int) $entry['year'] : 0;
            if ($year <= 0) {
                continue;
            }
            $total = (int) ($entry['total'] ?? $entry['nbDays'] ?? $entry['jeunNbDays'] ?? 0);
            $done = (int) ($entry['done'] ?? $entry['jeunNbDaysR'] ?? $entry['doneDays'] ?? 0);
            $total = max(0, $total);
            $done = max(0, min($total, $done));

            $map[$year] = [
                'year' => $year,
                'total' => $total,
                'done' => $done,
            ];
        }

        if ($map === []) {
            return [];
        }

        ksort($map);
        return array_values($map);
    }
}
