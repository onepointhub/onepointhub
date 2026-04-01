<?php

namespace Database\Seeders;

use App\Modules\Core\Models\Workspace;
use App\Modules\Projects\Models\ProjectTemplate;
use Illuminate\Database\Seeder;

class ProjectTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            $this->seedForWorkspace($workspace->id);
        }
    }

    protected function seedForWorkspace(int $workspaceId): void
    {
        $templates = [
            [
                'name' => 'Web Design Project',
                'description' => 'Standard web design workflow with discovery, design, development, and launch phases.',
                'structure' => [
                    'milestones' => [
                        ['name' => 'Discovery', 'due_offset_days' => 7],
                        ['name' => 'Design', 'due_offset_days' => 21],
                        ['name' => 'Development', 'due_offset_days' => 49],
                        ['name' => 'Launch', 'due_offset_days' => 56],
                    ],
                    'tasks' => [
                        ['title' => 'Client kick-off meeting', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Gather requirements and content', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Wireframes', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 1],
                        ['title' => 'Visual design — desktop', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 1],
                        ['title' => 'Visual design — mobile', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 1],
                        ['title' => 'Design approval', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 1],
                        ['title' => 'Frontend build', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 2],
                        ['title' => 'CMS integration', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 2],
                        ['title' => 'QA testing', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 2],
                        ['title' => 'Deploy to staging', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 2],
                        ['title' => 'Client review and sign-off', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 3],
                        ['title' => 'Deploy to production', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 3],
                    ],
                ],
                'is_builtin' => true,
            ],
            [
                'name' => 'Software Sprint',
                'description' => '2-week agile sprint template with backlog grooming, development, and retrospective.',
                'structure' => [
                    'milestones' => [
                        ['name' => 'Sprint Planning', 'due_offset_days' => 1],
                        ['name' => 'Sprint Execution', 'due_offset_days' => 12],
                        ['name' => 'Sprint Review', 'due_offset_days' => 14],
                    ],
                    'tasks' => [
                        ['title' => 'Backlog grooming', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Sprint planning meeting', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Feature development', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 1],
                        ['title' => 'Code review', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 1],
                        ['title' => 'Write tests', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 1],
                        ['title' => 'Deploy to staging', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 1],
                        ['title' => 'Sprint demo', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 2],
                        ['title' => 'Retrospective', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 2],
                    ],
                ],
                'is_builtin' => true,
            ],
            [
                'name' => 'Monthly Retainer',
                'description' => 'Recurring monthly work structure with planning, execution, and reporting phases.',
                'structure' => [
                    'milestones' => [
                        ['name' => 'Month Start', 'due_offset_days' => 3],
                        ['name' => 'Mid-Month Check-in', 'due_offset_days' => 15],
                        ['name' => 'Month End', 'due_offset_days' => 30],
                    ],
                    'tasks' => [
                        ['title' => 'Monthly planning call', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Set priorities for the month', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 0],
                        ['title' => 'Execute deliverables', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 1],
                        ['title' => 'Mid-month check-in call', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 1],
                        ['title' => 'Prepare monthly report', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 2],
                        ['title' => 'End of month review', 'status' => 'todo', 'priority' => 'medium', 'milestone_index' => 2],
                        ['title' => 'Invoice preparation', 'status' => 'todo', 'priority' => 'high', 'milestone_index' => 2],
                    ],
                ],
                'is_builtin' => true,
            ],
        ];

        foreach ($templates as $template) {
            ProjectTemplate::firstOrCreate(
                ['workspace_id' => $workspaceId, 'name' => $template['name'], 'is_builtin' => true],
                array_merge($template, ['workspace_id' => $workspaceId]),
            );
        }
    }
}
