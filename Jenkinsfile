pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        SSH_KEY = credentials('prod_ssh_key_id')
        AWS_INSTANCE_IP = '3.99.241.216'
        GIT_BRANCH = 'master' // Change this to your desired branch
        MYSQL_ROOT_PASSWORD = 'sai'
        MYSQL_DATABASE = 'cms_db'
        MYSQL_USER = 'sai'
        MYSQL_PASSWORD = 'sai'
        DOCKER_NETWORK = 'php-network'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout([$class: 'GitSCM',
                    branches: [
                        [name: "*/$GIT_BRANCH"]
                    ],
                    doGenerateSubmoduleConfigurations: false,
                    extensions: [
                        [$class: 'CleanCheckout']
                    ],
                    submoduleCfg: [],
                    userRemoteConfigs: [
                        [url: 'https://github.com/saikiran19999/logistics-supply.git']
                    ]
                ])
            }
        }

        stage('Build Docker Images') {
            steps {
                script {
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} build"
                }
            }
        }

        stage('Push Docker Images to Docker Hub') {
            steps {
                script {
                    withCredentials([
                        [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
                    ]) {
                        sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
                    }

                    // Tagging and pushing the images
                    sh "docker-compose -f ${DOCKER_COMPOSE_FILE} push"
                }
            }
        }

        stage('Deploy to EC2') {
            steps {
                script {
                    withCredentials([
                        [$class: 'UsernamePasswordMultiBinding', credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']
                    ]) {
                        sh "docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD"
                    }
			sh "docker-compose -f ${DOCKER_COMPOSE_FILE} up"
                }
            }
        }
    }
}
