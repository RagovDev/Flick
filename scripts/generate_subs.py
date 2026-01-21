import sys
import json
import whisper
import warnings

# Ignorar advertencias de FP16 si no tienes GPU configurada
warnings.filterwarnings("ignore")

def generate_subtitles(video_path):
    try:
        # Usamos el modelo 'base' (equilibrio velocidad/calidad)
        model = whisper.load_model("base")
        result = model.transcribe(video_path, fp16=False)

        formatted_segments = []
        for segment in result['segments']:
            formatted_segments.append({
                "start": round(segment['start'], 2),
                "end": round(segment['end'], 2),
                "text": segment['text'].strip()
            })

        print(json.dumps(formatted_segments))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Falta la ruta del video"}))
        sys.exit(1)

    video_file_path = sys.argv[1]
    generate_subtitles(video_file_path)