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

local PipelineBuildForUAT = {
    kind: "pipeline",
    type: "docker",
    name: "Build for UAT stage",
    trigger: {
        branch: ['uat'],
        event: ['push']
    },
    steps: [
        {
            name: "Install",
            image: "composer",
            commands: [
                "composer install"
            ]
        }
    ],
};

local PipelineDeployToUAT = {
    kind: "pipeline",
    type: "docker",
    name: "Deploy to UAT stage",
    trigger: {
        event: ["promote"],
        target: ['UAT'],
    },
    steps: [
        {
            name: "Build for UAT",
            image: "alpine",
            commands: [
                "echo \"start build for UAT\"",
                "ls -al"
            ]
        }
    ]
};

[
    PipelineRequestCodeReview,
    PipelineBuildForUAT,
    PipelineDeployToUAT
]