local PipelineBuild = {
    kind: "pipeline",
    type: "docker",
    name: "build",
    steps: [
        {
            name: "Build",
            image: "alpine",
            commands: [
                "echo start build on PR labeled"
            ]
        }
    ],
    // trigger: {
    //     event: ['pull_request'],
    //     action: ['labeled'],
    // }
};

[
    PipelineBuild,
]