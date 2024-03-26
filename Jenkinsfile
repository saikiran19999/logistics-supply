pipeline {
  agent any
    
  stages {
    stage ('Run Docker Compose') {
      steps{
        sh 'docker-compose build'
        sh 'docker-compose up -d'
      }
    }
  }
}