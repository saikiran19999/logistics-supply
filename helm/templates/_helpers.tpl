{{/* Generate a unique name for the chart resources */}}
{{- define "my-chart.fullname" -}}
  {{- printf "%s-%s" .Release.Name .Chart.Name | trunc 63 | trimSuffix "-" -}}
{{- end -}}
