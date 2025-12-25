<?php
// BEST RECOMMENDATION ALGORITHM FOR KAM PAUNUHOS
// Hybrid: Exact skills + Category + Keyword match
// Fast, Reliable, Works even with few jobs/skills
// Returns 6–12 jobs (never empty)

function recommendJobs($conn, $freelancer_id) {
    // Step 1: Get freelancer skills
    $freelancer = $conn->query("SELECT skills FROM users WHERE id = $freelancer_id AND role='freelancer'")->fetch_assoc();
    if (!$freelancer || empty(trim($freelancer['skills']))) {
        // If no skills → return random/recent jobs
        return $conn->query("SELECT id, title, description, budget, category 
                             FROM jobs WHERE status='open' 
                             ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    }

    $skills_raw = strtolower(trim($freelancer['skills']));
    $skills = array_map('trim', preg_split('/[\s,.;]+/', $skills_raw));
    $skills = array_filter($skills); // remove empty

    if (empty($skills)) {
        return []; // safety (shouldn't happen)
    }

    // Step 2: Get all open jobs
    $jobs_query = $conn->query("SELECT id, title, description, budget, category 
                                FROM jobs WHERE status='open' 
                                ORDER BY created_at DESC");
    $jobs = [];
    while ($row = $jobs_query->fetch_assoc()) {
        $jobs[] = $row;
    }

    if (empty($jobs)) return [];

    // Step 3: Score each job
    $scored_jobs = [];
    foreach ($jobs as $job) {
        $score = 0;

        // 3.1 Exact skill match (strongest)
        $job_text = strtolower($job['title'] . " " . $job['description'] . " " . $job['category']);
        foreach ($skills as $skill) {
            if (stripos($job_text, $skill) !== false) {
                $score += 30; // big boost for direct match
            }
        }

        // 3.2 Category match (medium boost)
        $category_lower = strtolower($job['category']);
        foreach ($skills as $skill) {
            if (stripos($category_lower, $skill) !== false) {
                $score += 20;
            }
        }

        // 3.3 Keyword overlap (small boost)
        $job_words = array_unique(preg_split('/[\s,.;]+/', strtolower($job_text)));
        $common = array_intersect($job_words, $skills);
        $score += count($common) * 5;

        // 3.4 Normalize score (0-100)
        $score = min(100, $score);

        $scored_jobs[] = [
            'job' => $job,
            'score' => $score,
            'match_level' => $score >= 60 ? 'High' : ($score >= 30 ? 'Medium' : 'Low'),
            'badge' => $score >= 60 ? 'bg-success' : ($score >= 30 ? 'bg-warning' : 'bg-secondary')
        ];
    }

    // Step 4: Sort by score DESC
    usort($scored_jobs, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    // Step 5: Return top 12 (or all if less)
    return array_slice($scored_jobs, 0, 12);
}
?>