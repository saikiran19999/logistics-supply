pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        AWS_INSTANCE_IP = '3.96.170.84'
        DOCKER_HUB_USERNAME = 'your_dockerhub_username'
        GIT_REPO_URL = 'https://github.com/saikiran19999/logistics-supply.git'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout([$class: 'GitSCM',
                    branches: [
                        [name: "*/master"]
                    ],
                    doGenerateSubmoduleConfigurations: false,
                    extensions: [
                        [$class: 'CleanCheckout']
                    ],
                    submoduleCfg: [],
                    userRemoteConfigs: [
                        [url: GIT_REPO_URL]
                    ]
                ])
            }
        }

        stage('Build and Push Docker Images') {
            steps {
                script {
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} push"
                }
            }
        }

        stage('Deploy to EC2') {
            steps {
                script {
                    sshagent(['prod_ssh_key_id']) {
                        sh "scp -o StrictHostKeyChecking=no ${DOCKER_COMPOSE_FILE} ec2-user@${AWS_INSTANCE_IP}:~/"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ${DOCKER_COMPOSE_FILE} pull'"
                        sh "ssh -o StrictHostKeyChecking=no ec2-user@${AWS_INSTANCE_IP} 'docker-compose -f ${DOCKER_COMPOSE_FILE} up -d'"
                    }
                }
            }
        }
    }
}
