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
        event: ['pull_request'],
        action: ['labeled'],
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

local PipelineRequestCodeReview = {
    kind: "pipeline",
    type: "docker",
    name: "Request code review",
    trigger: {
        event: ["custom"],
    },
    steps: [
        {
            name: "Send notification",
            image: "alpine",
            commands: [
                "echo \"start send notification\"",
                "env",
                "echo \"This is triggered by a custom event cp:${custom_param} dcp: ${DRONE_CUSTOM_PARAM} dce: ${DRONE_CUSTOM_ENV}\"",
            ]
        }
    ]
};

local PipelineDeployToTest = {
    kind: "pipeline",
    type: "docker",
    name: "Deploy to test stage",
    trigger: {
        event: ["promote"],
        target: ['test'],
    },
    steps: [
        {
            name: "Build for test",
            image: "alpine",
            commands: [
                "echo \"start build for test\"",
            ]
        }
    ]
}

[
    PipelineBasic,
    PipelineBuild,
    PipelineRequestCodeReview,
    PipelineDeployToTest
]