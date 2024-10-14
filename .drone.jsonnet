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
    trigger: {
        event: ['pull_request'],
        action: ['opened', 'synchronize'],
    }
};

local PipelineBasic = {
    kind: "pipeline",
    type: "docker",
    name: "start_drone",
    steps: [
        {
            name: "Start Run Drone",
            image: "alpine",
            commands: [
                "echo start run drone"
            ]
        }
    ],
};

[
    PipelineBasic,
    PipelineBuild,
]