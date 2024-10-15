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
            ],
            // when: {
            //     event: ['pull_request']
            // }
        }
    ],
    trigger: {
    //     branch: ['main'],
        event: ['pull_request'],
    //     action: ['opened', 'synchronized', 'closed', 'reopened'],
    }
};

local PipelineBasic = {
    kind: "pipeline",
    type: "docker",
    name: "start_drone",
    trigger: {
       event: ['push']
    },
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