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
                "echo \"This is triggered by a custom event ${DRONE_CUSTOM_PARAM} ${DRONE_CUSTOM_ENV}\"",
            ]
        }
    ]
};

[
    PipelineBasic,
    PipelineBuild,
    PipelineRequestCodeReview
]