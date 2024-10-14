local PipelineBuild = {
    kind: "pipeline",
    name: "build",
    steps: [
        {
            name: "Call wecom",
            commands: [
                "curl -s https://wecome"
            ]
        }
    ],
    trigger: {
        event: ['pull_request'],
        action: ['labeled'],
    }
};

[
    PipelineBuild,
]